<?php

namespace Padam87\SimplePayBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Padam87\SimplePayBundle\Entity\Item;
use Padam87\SimplePayBundle\Entity\Transaction;
use Padam87\SimplePayBundle\Exception\ErrorCodeException;
use Padam87\SimplePayBundle\Exception\InvalidSignatureException;
use Padam87\SimplePayBundle\Exception\UnknownTransactionException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SimplePay
{
    private HttpClientInterface $client;
    private ConfigHelper $configHelper;
    private UrlGeneratorInterface $router;
    private ValidatorInterface $validator;
    private RequestStack $requestStack;
    private EntityManagerInterface $em;

    public function __construct(
        HttpClientInterface $client,
        ConfigHelper $configHelper,
        UrlGeneratorInterface $router,
        ValidatorInterface $validator,
        RequestStack $requestStack,
        EntityManagerInterface $em
    ) {
        $this->client = $client;
        $this->configHelper = $configHelper;
        $this->router = $router;
        $this->validator = $validator;
        $this->requestStack = $requestStack;
        $this->em = $em;
    }

    public function start(Transaction $transaction, ?string $cardSecret = null): array
    {
        $violations = $this->validator->validate($transaction);

        if ($violations->count() > 0) {
            throw new \Exception('Transaction must be valid');
        }

        $merchant = $this->configHelper->getMerchant($transaction->getCurrency());

        $transaction->setMerchant($merchant['id']);

        $backref = $this->router->generate(
            $this->configHelper->get('backref_route'),
            $this->getRouteParameters($transaction, $this->configHelper->get('backref_route_parameters')),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $data = [
            'salt' => $transaction->getSalt(),
            'merchant' => $transaction->getMerchant(),
            'orderRef' => $transaction->getOrderRef(),
            'currency' => $transaction->getCurrency(),
            'customerEmail' => $transaction->getCustomerEmail(),
            'language' => $transaction->getLanguage(),
            'sdkVersion' => '@Padam87\SimplePayBundle',
            'methods' => $transaction->getMethods(),
            //'total' => null, // not used, overwritten by items
            'timeout' => @date("c", time() + $transaction->getTimeout()),
            'url' => $backref,
            'invoice' => $transaction->getInvoice()->toArray(),
            'delivery' => $transaction->getDelivery() ? $transaction->getDelivery()->toArray() : null,
            'shippingPrice' => $transaction->getShippingPrice(),
            'discount' => $transaction->getDiscount(),
        ];

        if ($cardSecret) {
            $data['cardSecret'] = $cardSecret;
            $data['threeDSReqAuthMethod'] = '02';
        }

        $data = array_filter($data, function ($value) {
            return $value != null;
        });

        /** @var Item $item */
        foreach ($transaction->getItems() as $item) {
            $data['items'][] = $item->toArray();
        }

        $response = $this->client->request(
            'POST',
            $this->configHelper->getStartUrl(),
            [
                'json' => $data,
                'headers' => [
                    'Signature' => $this->getSignature($merchant['secret'], $data),
                ],
            ]
        );

        $responseData = $response->toArray();

        if (array_key_exists('errorCodes', $responseData)) {
            throw new ErrorCodeException($responseData['errorCodes']);
        }

        if (null !== $old = $this->em->find($this->configHelper->getTransactionEntity(), $responseData['transactionId'])) {
            $old->setStatus(Transaction::STATUS_STARTED);
        } else {
            $transaction
                ->setId($responseData['transactionId'])
                ->setStatus(Transaction::STATUS_STARTED)
            ;

            $this->em->persist($transaction);
        }

        return $responseData;
    }

    private function getRouteParameters(Transaction $transaction, array $config): array
    {
        $accessor = PropertyAccess::createPropertyAccessor();

        $parameters = [];
        foreach ($config as $name => $path) {
            $parameters[$name] = $accessor->getValue($transaction, $path);
        }

        return $parameters;
    }

    public function backref(?Request $request = null): Transaction
    {
        if ($request === null) {
            $request = $this->requestStack->getCurrentRequest();
        }

        $data = json_decode(base64_decode($request->get('r')), true);

        $merchant = $this->configHelper->getMerchantById($data['m']);

        if ($this->getSignature($merchant['secret'], $data) != $request->get('s')) {
            throw new InvalidSignatureException();
        }

        if (null === $transaction = $this->em->find($this->configHelper->getTransactionEntity(), $data['t'])) {
            throw new UnknownTransactionException($data['t']);
        }

        $transaction
            ->setResponseCode($data['r'])
            ->setStatus($data['e'])
        ;

        return $transaction;
    }

    public function ipn(?Request $request = null): Transaction
    {
        if ($request === null) {
            $request = $this->requestStack->getCurrentRequest();
        }

        $signature = $request->headers->get('Signature');
        $data = json_decode($request->getContent(), true);

        $merchant = $this->configHelper->getMerchantById($data['merchant']);

        if ($this->getSignature($merchant['secret'], $data) != $signature) {
            throw new InvalidSignatureException();
        }

        if (null === $transaction = $this->em->find($this->configHelper->getTransactionEntity(), $data['transactionId'])) {
            throw new UnknownTransactionException($data['t']);
        }

        $transaction->setStatus($data['status']);

        return $transaction;
    }

    public function ipnResponse(?Request $request = null): Response
    {
        $data = json_decode($request->getContent(), true);
        $data['receiveDate'] = @date("c", time());

        $merchant = $this->configHelper->getMerchantById($data['merchant']);

        $response = new Response(json_encode($data));
        $response->headers->set('Signature', $this->getSignature($merchant['secret'], $data));

        return $response;
    }

    public function getSignature($secret, array $data)
    {
        return base64_encode(hash_hmac('sha384', json_encode($data), trim($secret), true));
    }
}
