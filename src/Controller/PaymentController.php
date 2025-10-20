<?php

namespace App\Controller;

use App\Entity\Payment;
use Payum\Core\Payum;
use Payum\Core\Request\GetHumanStatus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends AbstractController
{
    #[Route('/payment/choose', name: 'payment_choose')]
    public function choose(): Response
    {
        return $this->render('payment/choose.html.twig');
    }

    #[Route('/payment/prepare/{gateway}', name: 'payment_prepare')]
    public function prepare(string $gateway, Payum $payum): Response
    {
        $gatewayName = $gateway;
        $storage = $payum->getStorage(Payment::class);

        $payment = $storage->create();
        $payment->setNumber(uniqid());
        $payment->setCurrencyCode('EUR');
        $payment->setTotalAmount(1234); // 12.34 EUR
        $payment->setDescription('Commande InnovShop');

        $storage->update($payment);

        $captureToken = $payum->getTokenFactory()->createCaptureToken(
            $gatewayName,
            $payment,
            'payment_done'
        );

        return $this->redirect($captureToken->getTargetUrl());
    }

    #[Route('/payment/done', name: 'payment_done')]
    public function done(Request $request, Payum $payum): Response
    {
        $token = $payum->getHttpRequestVerifier()->verify($request);

        $gateway = $payum->getGateway($token->getGatewayName());

        $gateway->execute($status = new GetHumanStatus($token));

        $payment = $status->getFirstModel();

        $payum->getHttpRequestVerifier()->invalidate($token);

        return $this->render('payment/done.html.twig', [
            'status' => $status->getValue(),
            'payment' => $payment,
        ]);
    }
}