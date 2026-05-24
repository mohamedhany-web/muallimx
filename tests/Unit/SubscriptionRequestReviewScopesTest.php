<?php

namespace Tests\Unit;

use App\Models\SubscriptionRequest;
use PHPUnit\Framework\TestCase;

class SubscriptionRequestReviewScopesTest extends TestCase
{
    public function test_manual_review_requires_pending_with_payment_proof(): void
    {
        $req = new SubscriptionRequest([
            'status' => SubscriptionRequest::STATUS_PENDING,
            'payment_method' => 'wallet',
            'payment_proof' => 'payment-proofs/sample.jpg',
        ]);

        $this->assertTrue($req->requiresManualReview());
        $this->assertFalse($req->isOnlineGatewayPayment());
    }

    public function test_online_gateway_is_pending_online_without_proof(): void
    {
        $req = new SubscriptionRequest([
            'status' => SubscriptionRequest::STATUS_PENDING,
            'payment_method' => 'online',
            'payment_proof' => null,
        ]);

        $this->assertTrue($req->isOnlineGatewayPayment());
        $this->assertFalse($req->requiresManualReview());
    }

    public function test_approved_request_needs_neither_review_nor_gateway_auto(): void
    {
        $req = new SubscriptionRequest([
            'status' => SubscriptionRequest::STATUS_APPROVED,
            'payment_method' => 'online',
            'payment_proof' => null,
        ]);

        $this->assertFalse($req->requiresManualReview());
        $this->assertTrue($req->isOnlineGatewayPayment());
    }

    public function test_online_with_proof_is_neither_auto_nor_listed_manual(): void
    {
        $req = new SubscriptionRequest([
            'status' => SubscriptionRequest::STATUS_PENDING,
            'payment_method' => 'online',
            'payment_proof' => 'payment-proofs/x.jpg',
        ]);

        $this->assertFalse($req->isOnlineGatewayPayment());
        $this->assertTrue($req->requiresManualReview());
    }
}
