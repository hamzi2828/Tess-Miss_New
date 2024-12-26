<?php

namespace App\Services;

use App\Models\Merchant;
use App\Models\User;
use App\Mail\graphMailer;
use Exception;

class GraphMailersSender
{
    public function sendApprovalMail($merchantId, $message, $stage)
    {
        // Find the merchant
        $merchant = Merchant::findOrFail($merchantId);

        // Fetch users associated with the stage
        // $users = User::whereHas('department', function ($query) use ($stage) {
        //     $query->where('stage', $stage);
        // })->get();

        $users = User::where('role', 'user')
        ->whereHas('department', function ($query) use ($stage) {
            $query->where('stage', $stage);
        })->get();

        if ($users->isEmpty()) {
            return ['status' => 'error', 'message' => 'No users found for the specified stage.'];
        }

        $results = [];
        foreach ($users as $user) {

            // Prepare email details
            $emailArgs = [
                'subject' => 'Tess Payments ',
                'body' => '<p>Dear ' . htmlspecialchars($user->name) . ',</p>' .
                          '<p>' . htmlspecialchars($merchant->name) . $message . '.</p>' .
                          '<p>Best regards,<br>Your Company Team</p>',
                'toRecipients' => [
                    [
                        'address' => $user->email,
                    ],
                ],
            ];

            try {
                // Initialize graphMailer with environment variables
                $mailer = new graphMailer(
                    env('GRAPH_TENANT_ID'), // Tenant ID
                    env('GRAPH_CLIENT_ID'), // Client ID
                    env('GRAPH_CLIENT_SECRET') // Client Secret
                );

                // Sender email address
                $senderEmail = env('GRAPH_SENDER_EMAIL', 'info@tesspayments.com');

                // Send the email
                $mailer->sendMail($senderEmail, $emailArgs);

                $results[] = [
                    'user' => $user->id,
                    'status' => 'success',
                    'message' => 'Email sent successfully to ' . $user->email,
                ];
            } catch (Exception $e) {
                $results[] = [
                    'user' => $user->id,
                    'status' => 'error',
                    'message' => 'Error sending email: ' . $e->getMessage(),
                ];
            }
        }

        return $results;
    }


    public function senddeclinedMail($merchantId, $message, $stage)
    {
        // Find the merchant
        $merchant = Merchant::findOrFail($merchantId);

        // Fetch users associated with the stage
        // $users = User::whereHas('department', function ($query) use ($stage) {
        //     $query->where('stage', $stage);
        // })->get();

        $users = User::where('role', 'user')
        ->whereHas('department', function ($query) use ($stage) {
            $query->where('stage', $stage);
        })->get();

        if ($users->isEmpty()) {
            return ['status' => 'error', 'message' => 'No users found for the specified stage.'];
        }

        $results = [];
        foreach ($users as $user) {

            // Prepare email details
            $emailArgs = [
                'subject' => 'Tess Payments ',
                'body' => '<p>Dear ' . htmlspecialchars($user->name) . ',</p>' .
                          '<p>' . htmlspecialchars($merchant->name) . $message . '.</p>' .
                          '<p>Best regards,<br>Your Company Team</p>',
                'toRecipients' => [
                    [
                        'address' => $user->email,
                    ],
                ],
            ];

            try {
                // Initialize graphMailer with environment variables
                $mailer = new graphMailer(
                    env('GRAPH_TENANT_ID'), // Tenant ID
                    env('GRAPH_CLIENT_ID'), // Client ID
                    env('GRAPH_CLIENT_SECRET') // Client Secret
                );

                // Sender email address
                $senderEmail = env('GRAPH_SENDER_EMAIL', 'info@tesspayments.com');

                // Send the email
                $mailer->sendMail($senderEmail, $emailArgs);

                $results[] = [
                    'user' => $user->id,
                    'status' => 'success',
                    'message' => 'Email sent successfully to ' . $user->email,
                ];
            } catch (Exception $e) {
                $results[] = [
                    'user' => $user->id,
                    'status' => 'error',
                    'message' => 'Error sending email: ' . $e->getMessage(),
                ];
            }
        }

        return $results;
    }


    public function sendcreationMail($merchantId, $message, $stage)
    {
        // Find the merchant
        $merchant = Merchant::findOrFail($merchantId);

        // Fetch users associated with the stage
        // $users = User::whereHas('department', function ($query) use ($stage) {
        //     $query->where('stage', $stage);
        // })->get();

        $users = User::where('role', 'supervisor')
        ->whereHas('department', function ($query) use ($stage) {
            $query->where('stage', $stage);
        })->get();

        if ($users->isEmpty()) {
            return ['status' => 'error', 'message' => 'No users found for the specified stage.'];
        }

        $results = [];
        foreach ($users as $user) {

            // Prepare email details
            $emailArgs = [
                'subject' => 'Tess Payments ',
                'body' => '<p>Dear ' . htmlspecialchars($user->name) . ',</p>' .
                          '<p>' . htmlspecialchars($merchant->name) . $message . '.</p>' .
                          '<p>Best regards,<br>Your Company Team</p>',
                'toRecipients' => [
                    [
                        'address' => $user->email,
                    ],
                ],
            ];

            try {
                // Initialize graphMailer with environment variables
                $mailer = new graphMailer(
                    env('GRAPH_TENANT_ID'), // Tenant ID
                    env('GRAPH_CLIENT_ID'), // Client ID
                    env('GRAPH_CLIENT_SECRET') // Client Secret
                );

                // Sender email address
                $senderEmail = env('GRAPH_SENDER_EMAIL', 'info@tesspayments.com');

                // Send the email
                $mailer->sendMail($senderEmail, $emailArgs);

                $results[] = [
                    'user' => $user->id,
                    'status' => 'success',
                    'message' => 'Email sent successfully to ' . $user->email,
                ];
            } catch (Exception $e) {
                $results[] = [
                    'user' => $user->id,
                    'status' => 'error',
                    'message' => 'Error sending email: ' . $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}
