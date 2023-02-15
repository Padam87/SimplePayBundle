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
use Symfony\Contracts\HttpClient\ResponseInterface;

class SimplePay
{
    public function __construct(
        private HttpClientInterface $client,
        private ConfigHelper $configHelper,
        private UrlGeneratorInterface $router,
        private ValidatorInterface $validator,
        private RequestStack $requestStack,
        private EntityManagerInterface $em
    ) {
    }

    public function start(Transaction $transaction, ?string $cardSecret = null): array
    {
        $violations = $this->validator->validate($transaction);

        if ($violations->count() > 0) {
            throw new \Exception('Transaction must be valid');
        }

        $merchant = $this->configHelper->getMerchant($transaction->getCurrency());

        $transaction->setMerchant($merchant['id']);

        $data = $this->transactionToArray($transaction, $cardSecret);
        $data['url'] = $this->router->generate(
            $this->configHelper->get('backref_route'),
            $this->getRouteParameters($transaction, $this->configHelper->get('backref_route_parameters')),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        if ($transaction->getRecurring() !== null) {
            $data['recurring'] = $transaction->getRecurring()->toArray();
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

        $this->handleResponse($response, $transaction);

        return $response->toArray();
    }

    public function callDo(Transaction $transaction, ?string $cardSecret = null): array
    {
        $violations = $this->validator->validate($transaction);

        if ($violations->count() > 0) {
            throw new \Exception('Transaction must be valid');
        }

        $merchant = $this->configHelper->getMerchant($transaction->getCurrency());

        $transaction->setMerchant($merchant['id']);

        $data = $this->transactionToArray($transaction, $cardSecret);
        $data['cardId'] = $transaction->getCardId();
        $data['type'] = 'CIT';

        if ($transaction->getBrowser() !== null) {
            $data['browser'] = $transaction->getBrowser()->toArray();
        }

        $response = $this->client->request(
            'POST',
            $this->configHelper->getDoUrl(),
            [
                'json' => $data,
                'headers' => [
                    'Signature' => $this->getSignature($merchant['secret'], $data),
                ],
            ]
        );

        $this->handleResponse($response, $transaction);

        return $response->toArray();
    }

    public function doRecurring(Transaction $transaction): array
    {
        $violations = $this->validator->validate($transaction);

        if ($violations->count() > 0) {
            throw new \Exception('Transaction must be valid');
        }

        $merchant = $this->configHelper->getMerchant($transaction->getCurrency());

        $transaction->setMerchant($merchant['id']);

        $data = $this->transactionToArray($transaction);
        $data['token'] = $transaction->getToken();
        $data['type'] = 'MIT';
        $data['threeDSReqAuthMethod'] = '02';

        $response = $this->client->request(
            'POST',
            $this->configHelper->getDoRecurringUrl(),
            [
                'json' => $data,
                'headers' => [
                    'Signature' => $this->getSignature($merchant['secret'], $data),
                ],
            ]
        );

        $this->handleResponse($response, $transaction);

        return $response->toArray();
    }

    public function tokenCancel(string $token, string $currency): array
    {
        $merchant = $this->configHelper->getMerchant($currency);

        $data = [
            'token' => $token,
            'merchant' => $merchant['id'],
            'salt' => bin2hex(openssl_random_pseudo_bytes(16)),
            'sdkVersion' => '@Padam87\SimplePayBundle',
        ];

        $response = $this->client->request(
            'POST',
            $this->configHelper->getTokenCancelUrl(),
            [
                'json' => $data,
                'headers' => [
                    'Signature' => $this->getSignature($merchant['secret'], $data),
                ],
            ]
        );

        return $response->toArray();
    }

    public function cardCancel(string $cardId, string $currency): array
    {
        $merchant = $this->configHelper->getMerchant($currency);

        $data = [
            'cardId' => $cardId,
            'merchant' => $merchant['id'],
            'salt' => bin2hex(openssl_random_pseudo_bytes(16)),
            'sdkVersion' => '@Padam87\SimplePayBundle',
        ];

        $response = $this->client->request(
            'POST',
            $this->configHelper->getCardCancelUrl(),
            [
                'json' => $data,
                'headers' => [
                    'Signature' => $this->getSignature($merchant['secret'], $data),
                ],
            ]
        );

        return $response->toArray();
    }

    private function transactionToArray(Transaction $transaction, ?string $cardSecret = null): array
    {
        $data = [
            'salt' => $transaction->getSalt(),
            'merchant' => $transaction->getMerchant(),
            'orderRef' => $transaction->getOrderRef(),
            'currency' => $transaction->getCurrency(),
            'customerEmail' => $transaction->getCustomerEmail(),
            'language' => $transaction->getLanguage(),
            'sdkVersion' => '@Padam87\SimplePayBundle',
            'methods' => $transaction->getMethods(),
            'total' => (int) $transaction->getTotal() == $transaction->getTotal() ? (int) $transaction->getTotal() : $transaction->getTotal(),
            'timeout' => @date("c", time() + $transaction->getTimeout()),
            'invoice' => $transaction->getInvoice()->toArray(),
            'delivery' => $transaction->getDelivery() !== null ? $transaction->getDelivery()->toArray() : null,
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

        return $data;
    }

    private function handleResponse(ResponseInterface $response, Transaction $transaction)
    {
        $responseData = $response->toArray();

        if (array_key_exists('errorCodes', $responseData)) {
            // 3004: Redirect during 3DS challenge ... not really an error, just a case
            if (count($responseData['errorCodes']) > 1 || !in_array(3004, $responseData['errorCodes'])) {
                throw new ErrorCodeException($responseData['errorCodes']);
            }
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

        $data = json_decode(base64_decode((string) $request->get('r')), true);

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
            throw new UnknownTransactionException($data['transactionId']);
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
        return base64_encode(hash_hmac('sha384', json_encode($data), trim((string) $secret), true));
    }
}
