<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0; padding:20px; background-color:#f5f5f5; font-family:sans-serif;">
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td align="center">
                <table width="600" cellpadding="30" style="background:#fff; border-radius:8px;">
                    <tr>
                        <td style="border-bottom:2px solid #f0f0f0;">
                            <h1 style="margin:0 0 10px 0;">{{site_name}}</h1>
                            <p style="margin:0; color:#646970;">Plugin Deactivation Feedback</p>
                        </td>
                    </tr>
                    <tr><td><strong>User Email:</strong><br>{{user_email}}</td></tr>
                    <tr><td><strong>Deactivation Reason:</strong><br>{{reason}}</td></tr>
                    {{additional}}
                    <tr>
                        <td style="border-top:2px solid #f0f0f0; padding-top:15px; color:#888; font-size:13px;">
                            <p>Date: {{date}}</p>
                            <p>Site: <a href="{{site_url}}">{{site_url}}</a></p>
                            <p style="font-size:12px;">This is an automated notification. Do not reply.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
