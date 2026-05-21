<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2 style="color: #003527;">Undangan Tim - StokPintar</h2>
    <p>Halo,</p>
    <p>Anda telah diundang untuk bergabung dengan tim <strong>{{ $tenant->name }}</strong> sebagai <strong>{{ \App\Support\RolePermissionMap::labels()[$invitation->role] ?? $invitation->role }}</strong>.</p>
    
    <p>Klik tombol di bawah ini untuk menerima undangan dan mengatur password akun Anda (berlaku selama 48 jam):</p>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ url('/invite/' . $invitation->token) }}" style="background-color: #003527; color: #fff; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;">Terima Undangan</a>
    </div>
    
    <p>Jika Anda tidak merasa menerima undangan ini, Anda bisa mengabaikan email ini.</p>
    
    <hr style="border: none; border-top: 1px solid #eee; margin-top: 40px; margin-bottom: 20px;">
    <p style="font-size: 12px; color: #888;">&copy; {{ date('Y') }} StokPintar. Hak cipta dilindungi.</p>
</body>
</html>
