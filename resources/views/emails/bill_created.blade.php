<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Đơn hàng của bạn đã được tạo thành công</title>
</head>
<body>
    <h2>Cảm ơn bạn đã mua hàng tại {{ config('app.name') }}</h2>
    <p>Đơn hàng của bạn đã được tạo thành công. Dưới đây là chi tiết đơn hàng:</p>
    
    <h3>Chi tiết đơn hàng:</h3>
    <ul>
        <li><strong>Mã đơn hàng:</strong> {{ $bill->ma_bill }}</li>
        <li><strong>Tổng số tiền:</strong> {{ number_format($bill->total_amount, 0, ',', '.') }} VND</li>
        <li><strong>Ngày đặt hàng:</strong> {{ $bill->order_date }}</li>

    </ul>

    <p>Nếu bạn có bất kỳ câu hỏi nào, vui lòng liên hệ với chúng tôi qua email hoặc số điện thoại hỗ trợ.</p>
    
    <p>Trân trọng,<br>
    Đội ngũ {{ config('app.name') }}</p>
</body>
</html>
