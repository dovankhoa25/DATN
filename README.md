1. khởi động dự án
    - thống nhất các bảng có status : true(1) là hoạt động , failed(0) là ngưng hoạt động
2. role :
    - admin ( chức vụ toàn hệ thống)
    - qtv ( dưới qtv không dùng được 1 số module)
    - ctv ( dưới qtv,admin giới hạn quyền module)
    - các module cấp tùy thuộc : 
        (user , customer , category , subcategory , size , voucher , subcategory , table , product , payment , bill)

3. Bổ sung chức năng dự án 31/8 ( tách luồng )
    - oder_cart ( cart lưu trữ tại cửa hàng )
    - online_cart ( cart lưu trữ giỏ hàng online )
    - bill ( bổ sung cần thiết Tối ưu)
4. Bổ sung bảng : user_addres 
    - lưu lại địa chỉ người dùng
    - list ra khi đặt hàng để người dùng chọn thuận tiện trong list addres