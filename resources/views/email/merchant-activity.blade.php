<!-- resources/views/emails/merchant-activity.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>KYC Notification</title>
</head>
<body>
    <h2>{{ $message }}</h2>

    <p>Details:</p>
    <ul>
        <li>Merchant: {{ $merchant->name }}</li>
        <li>Added by: {{ $addedBy }}</li>
        <li>Date: {{ now()->format('Y-m-d H:i:s') }}</li>
    </ul>

    <p>Please review the KYC details in the system.</p>

    <p>Best regards,<br>
    {{ config('app.name') }}</p>
</body>
</html>
