# -*- coding: utf-8 -*-
import os

filepath = r"d:\PTIT\NAM3\KI-2\LTW\PHP\HUONG_DAN.md"
parts = []

def add_part(text):
    parts.append(text)

def write_file():
    with open(filepath, "w", encoding="utf-8") as f:
        f.write("\n\n".join(parts))
    print("Successfully wrote HUONG_DAN.md")

def add_header():
    add_part("""# 📘 Tài liệu Ôn thi Chi tiết từng dòng code — HRCore HR Management System

Tài liệu này được thiết kế đặc biệt để giúp bạn học thuộc lòng, hiểu sâu bản chất code và trả lời thi vấn đáp xuất sắc. Mỗi chức năng được bóc tách chi tiết qua các bước kèm theo code thực tế và giải thích chi tiết từng dòng/khối lệnh theo phong cách dễ nhớ nhất.

---

## 📂 Phần 1: Ý nghĩa các Thư mục & Tiệp tin chính (Folder Structure)

> [!NOTE]
> Khi giáo viên hỏi: *"Cấu trúc thư mục của dự án này hoạt động như thế nào?"*, bạn trả lời như sau:
> Dự án được viết theo cấu trúc MVC (Model - View - Controller) tự dựng, giúp tách biệt giao diện và logic xử lý cơ sở dữ liệu:
>
> 1. **`app/`**: Thư mục chứa toàn bộ mã nguồn ứng dụng (Back-End & Front-End).
>    - [app/controllers/](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers): Chứa các lớp Controller để tiếp nhận request từ Client, kiểm tra quyền hạn, xử lý dữ liệu và render view hoặc trả về dữ liệu dạng JSON.
>    - [app/models/](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/models): Chứa các lớp Model kết nối cơ sở dữ liệu qua PDO, thực thi các truy vấn SQL nghiệp vụ.
>    - [app/views/](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/views): Chứa các tệp giao diện PHP + HTML hiển thị cho người dùng, chia theo từng thư mục chức năng.
>    - [app/core/](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/core): Chứa nhân hệ thống (Core Engine).
>      - [Auth.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/core/Auth.php): Quản lý session đăng nhập, kiểm tra phân quyền (Admin / Employee).
>      - [Session.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/core/Session.php): Tiện ích đọc, ghi và xóa Session PHP.
>      - [Database.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/core/Database.php): Thiết lập kết nối cơ sở dữ liệu thông qua PHP Data Objects (PDO).
>      - [Model.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/core/Model.php): Lớp cha của các Model, cung cấp các hàm dùng chung để truy vấn SQL (`query`, `queryOne`, `execute`).
>      - [Controller.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/core/Controller.php): Lớp cha của các Controller, chứa phương thức `render()` để nạp view, `json()` để trả về AJAX và các hàm lọc request đầu vào (`post()`, `get()`).
>      - [Router.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/core/Router.php): Bộ định tuyến URL, phân tích HTTP Method và URI từ trình duyệt để gọi đúng Controller/Action tương ứng.
> 2. **`public/`**: Thư mục chứa các tài nguyên tĩnh công khai (CSS, JS).
>    - [public/css/](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/public/css): Chứa các tệp stylesheet định dạng giao diện đã được tách biệt khỏi các View.
>    - [public/js/](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/public/js): Chứa các tệp JavaScript/jQuery để bắt sự kiện client và xử lý AJAX.
> 3. **`index.php`**: Entry point (Điểm khởi đầu). Mọi request từ trình duyệt đều đi qua file này để khởi chạy ứng dụng (`Router->dispatch()`).
> 4. **`CreateDB.sql`**: Tệp lệnh SQL để thiết lập cơ sở dữ liệu ban đầu cho dự án.

---

## ⚙️ Phần 2: Luồng hoạt động Back-End (BE) & Bản chất "Bắt sự kiện"

### 1. Luồng hoạt động Back-End (Cái gì gọi cái gì?)
> [!IMPORTANT]
> Khi người dùng bấm vào một liên kết hoặc nút bấm trên giao diện, luồng dữ liệu chạy ngầm trong hệ thống như sau:
> 1.  **Client (Trình duyệt):** Gửi HTTP Request (`GET` hoặc `POST`) chứa dữ liệu đầu vào.
> 2.  **Bộ định tuyến [Router.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/core/Router.php):** Phân tích URI và Method để gọi đúng Controller.
> 3.  **Bộ điều khiển (Controller):** 
>     *   Kiểm tra đăng nhập/phân quyền qua [Auth.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/core/Auth.php).
>     *   Làm sạch và nhận dữ liệu qua `$this->post()` hoặc `$this->get()` trong [Controller.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/core/Controller.php).
>     *   Validate dữ liệu đầu vào (nếu có lỗi thì báo ngay về giao diện).
> 4.  **Mô hình dữ liệu (Model):** Được Controller gọi để truy vấn Database. Model gọi các phương thức PDO của lớp cha [Model.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/core/Model.php) để lấy dữ liệu.
> 5.  **Cơ sở dữ liệu (MySQL):** Thực thi câu lệnh SQL, trả kết quả về Model ➔ Controller.
> 6.  **Kết quả:** Controller gọi `render()` để trả về giao diện HTML hoặc gọi `json()` để trả về chuỗi JSON (đối với AJAX).

### 2. Bản chất "Bắt sự kiện" trong ứng dụng Web
> [!TIP]
> *   **Phía Client (Trình duyệt):** Bắt các sự kiện tương tác vật lý của người dùng trên DOM (như click chuột `click`, gửi form `submit`, gõ phím `input`, thay đổi lựa chọn `change`) bằng JavaScript/jQuery.
> *   **Phía Server (PHP):** Không nghe trực tiếp được sự kiện chuột của người dùng. PHP bắt hành động thông qua việc nhận và phân tích Request HTTP gửi lên:
>     *   *Bắt HTTP Method:* Kiểm tra nếu phương thức nhận là POST (`$this->isPost()` / `$_SERVER['REQUEST_METHOD'] === 'POST'`) để xử lý lưu trữ.
>     *   *Bắt ngoại lệ (Exception):* Sử dụng khối lệnh `try { ... } catch (Exception $e) { ... }` để bắt các lỗi phát sinh ngoài dự kiến (như lỗi kết nối DB, lỗi trùng lặp khóa ngoại) để hiển thị thông báo lỗi thân thiện thay vì làm sập trang web.""")

def add_login():
    add_part("""## 🔍 Phần 3: Chi tiết Sự kiện & Logic của 10 Chức năng cốt lõi

---

### 🚪 1. Đăng nhập (`/auth/login`)

#### Bước 1: Client gửi dữ liệu
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Khi người dùng điền Email, mật khẩu và click Đăng nhập, trình duyệt lắng nghe sự kiện submit, ngăn chặn click đúp bằng cách đổi chữ trên nút, rồi gửi dữ liệu lên Server bằng POST."
> * **File giao diện:** [login.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/views/auth/login.php)

```html
<form method="POST" action="<?= BASE_URL ?>/auth/login" id="loginForm">
    <input type="email" name="email" required>
    <input type="password" name="password" id="password" required>
    <button type="submit" id="loginBtn">Đăng nhập</button>
</form>
```
```javascript
document.getElementById('loginForm').addEventListener('submit', function() {
    document.getElementById('loginBtn').textContent = 'Đang xử lý...';
});
```

=> giải thích: Đoạn code giao diện và bắt sự kiện đăng nhập
1. Khai báo form đăng nhập
`<form method="POST" action="<?= BASE_URL ?>/auth/login" id="loginForm">`
Giải thích:
Thẻ `form` khai báo gửi bằng phương thức `POST` (bảo mật khi truyền mật khẩu) và thuộc tính `action` dẫn tới route xử lý đăng nhập `/auth/login`.
Ví dụ:
Khi gửi form, các thông tin sẽ được truyền ẩn qua request body thay vì hiện trên thanh địa chỉ URL.

2. Ô nhập Email
`<input type="email" name="email" required>`
Giải thích:
Ô nhập Email. Thuộc tính `name="email"` dùng để server nhận diện làm khóa POST và `required` bắt buộc nhập định dạng email hợp lệ phía client.
Ví dụ:
Nếu bỏ trống ô này và bấm nút gửi, trình duyệt sẽ hiển thị cảnh báo "Please fill out this field".

3. Ô nhập mật khẩu
`<input type="password" name="password" id="password" required>`
Giải thích:
Ô nhập mật khẩu ẩn ký tự, có `name="password"` làm khóa và thuộc tính `required` bắt buộc nhập.
Ví dụ:
Mật khẩu nhập vào sẽ hiển thị dưới dạng dấu chấm hoặc dấu sao.

4. Nút đăng nhập
`<button type="submit" id="loginBtn">Đăng nhập</button>`
Giải thích:
Nút bấm kích hoạt hành động gửi form (sự kiện `submit`).

5. Lắng nghe sự kiện submit
`document.getElementById('loginForm').addEventListener('submit', function() { ... })`
Giải thích:
Đoạn mã JavaScript tìm kiếm form có id `loginForm` và gắn trình lắng nghe sự kiện `submit` để thực thi khi người dùng bấm gửi form.

6. Đổi chữ trên nút
`document.getElementById('loginBtn').textContent = 'Đang xử lý...';`
Giải thích:
Đổi chữ nút đăng nhập thành "Đang xử lý..." để ngăn chặn hành vi click đúp liên tục của người dùng.

---

#### Bước 2: Controller bắt request POST
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Tại máy chủ, AuthController bắt request xem phương thức gửi lên có phải là POST hay không bằng cách gọi hàm tiện ích isPost()."
> * **File xử lý:** [AuthController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/AuthController.php) (Dòng 24)

```php
if ($this->isPost()) {
```

=> giải thích: Bắt request phương thức POST
1. Kiểm tra phương thức request
`if ($this->isPost()) {`
Giải thích:
Kiểm tra xem request gửi lên có phương thức là `POST` hay không. Hàm `isPost()` kiểm tra biến siêu toàn cục `$_SERVER['REQUEST_METHOD'] === 'POST'`.
Ví dụ:
Nếu người dùng vừa submit form thì request method sẽ là POST, khi đó PHP mới chạy vào khối code xử lý đăng nhập bên trong.

---

#### Bước 3: Validate & lấy dữ liệu
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Controller tiến hành lấy thông tin từ POST, làm sạch khoảng trắng bằng trim() và kiểm tra dữ liệu xem có bị bỏ trống hay không."
> * **File xử lý:** [AuthController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/AuthController.php) (Dòng 25-30)

```php
$email    = trim($this->post('email', ''));
$password = $this->post('password', '');

if (empty($email) || empty($password)) {
    $errors[] = 'Vui lòng nhập email và mật khẩu.';
}
```

=> giải thích: Lấy dữ liệu và kiểm tra tính hợp lệ
1. Lấy và làm sạch Email
`$email = trim($this->post('email', ''));`
Giải thích:
Gọi phương thức `$this->post('email', '')` từ lớp cha `Controller.php` để lấy giá trị từ mảng `$_POST['email']`. Sau đó, hàm `trim()` loại bỏ toàn bộ khoảng trắng thừa ở hai đầu email.
Ví dụ:
Nếu người dùng nhập `" abc@gmail.com "` thì sau khi trim sẽ thành `"abc@gmail.com"`.

2. Lấy mật khẩu
`$password = $this->post('password', '');`
Giải thích:
Lấy giá trị của mật khẩu từ `$_POST['password']`, mặc định là chuỗi rỗng nếu trống.

3. Kiểm tra rỗng
`if (empty($email) || empty($password)) {`
Giải thích:
Kiểm tra nếu một trong hai biến `$email` hoặc `$password` bị trống bằng hàm `empty()`.

4. Thêm lỗi vào mảng
`$errors[] = 'Vui lòng nhập email và mật khẩu.';`
Giải thích:
Đẩy chuỗi thông báo lỗi vào mảng `$errors` để trả về giao diện.

---

#### Bước 4: Gọi Model truy vấn thông tin
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Khi dữ liệu đầu vào đầy đủ, Controller gọi UserModel truy tìm thông tin nhân viên trong CSDL tương ứng với email đã nhập."
> * **File xử lý:** [AuthController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/AuthController.php) (Dòng 31)

```php
$user = $this->userModel->findByEmail($email);
```

=> giải thích: Gọi Model tìm tài khoản bằng Email
1. Gọi hàm Model
`$user = $this->userModel->findByEmail($email);`
Giải thích:
Controller gọi phương thức `findByEmail()` từ đối tượng `$this->userModel` (một thể hiện của lớp `UserModel`) và truyền vào biến `$email` làm tham số. Phương thức sẽ trả về mảng thông tin tài khoản hoặc `null` nếu không tìm thấy.
Ví dụ:
Nếu email là `"admin@ptit.com"`, Model sẽ tìm trong database xem có tài khoản nào tương ứng không.

---

#### Bước 5: Model thực thi SQL
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "UserModel chạy câu lệnh SQL SELECT lấy thông tin người dùng từ bảng users theo Email được chỉ định, lọc bỏ các tài khoản đã bị xóa mềm."
> * **File xử lý:** [UserModel.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/models/UserModel.php) (Dòng 22-27)

```php
public function findByEmail(string $email): ?array {
    return $this->queryOne(
        "SELECT * FROM users WHERE email = ? AND deleted_at IS NULL",
        [$email]
    );
}
```

=> giải thích: Lớp Model tìm người dùng theo Email
`public function findByEmail(string $email): ?array`
Hàm tìm kiếm tài khoản người dùng theo email. Nhận vào tham số `$email` kiểu string và trả về một mảng (`array`) hoặc `null` (`?array`).

1. Chạy câu SQL SELECT và trả về 1 bản ghi
`return $this->queryOne("SELECT * FROM users WHERE email = ? AND deleted_at IS NULL", [$email]);`
Giải thích:
Gọi phương thức `queryOne()` của lớp cha `Model.php` để thực thi câu lệnh SQL.
Lệnh SQL tìm kiếm mọi trường thông tin từ bảng `users` thỏa mãn điều kiện `email` khớp với tham số truyền vào (`email = ?`) và tài khoản chưa bị xóa mềm (`deleted_at IS NULL`). Dấu hỏi chấm `?` giúp chống lại lỗi bảo mật SQL Injection.
Ví dụ:
Nếu CSDL có dòng email `"admin@ptit.com"`, hàm sẽ trả về mảng dữ liệu của dòng đó.

---

#### Bước 6: Trả kết quả và xác thực Session
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Nếu người dùng tồn tại và khớp mật khẩu băm thông qua password_verify(), hệ thống ghi nhận session đăng nhập thành công, ghi log và chuyển hướng về trang chủ."
> * **File xử lý:** [AuthController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/AuthController.php) (Dòng 33-41)

```php
if (!$user || !$this->userModel->verifyPassword($password, $user['password_hash'])) {
    $errors[] = 'Email hoặc mật khẩu không chính xác.';
} elseif ($user['status'] !== 'ACTIVE') {
    $errors[] = 'Tài khoản của bạn đã bị khóa hoặc vô hiệu hóa.';
} else {
    Auth::login($user);
    $this->userModel->recordLastLogin((int)$user['id']);
    $this->redirect('/home');
}
```

=> giải thích: Xác thực thông tin đăng nhập và lưu Session
1. Kiểm tra tài khoản và mật khẩu
`if (!$user || !$this->userModel->verifyPassword($password, $user['password_hash'])) {`
Giải thích:
So khớp mật khẩu thuần và chuỗi băm bảo mật bcrypt thông qua hàm `password_verify()` được bọc trong `verifyPassword()`. Nếu người dùng không tồn tại hoặc mật khẩu không khớp.

2. Báo lỗi sai thông tin
`$errors[] = 'Email hoặc mật khẩu không chính xác.';`
Giải thích:
Gán thông báo lỗi đăng nhập sai thông tin vào mảng `$errors`.

3. Kiểm tra trạng thái hoạt động của tài khoản
`elseif ($user['status'] !== 'ACTIVE') {`
Giải thích:
Kiểm tra nếu trạng thái tài khoản khác `'ACTIVE'` (đã bị khóa).

4. Báo lỗi tài khoản bị khóa
`$errors[] = 'Tài khoản của bạn đã bị khóa hoặc vô hiệu hóa.';`
Giải thích:
Đẩy thông báo lỗi khóa tài khoản vào mảng.

5. Lưu Session đăng nhập
`Auth::login($user);`
Giải thích:
Gọi phương thức tĩnh của lớp `Auth.php` để lưu trữ thông tin nhận dạng nhân sự vào Session (`$_SESSION['user_id']`, `$_SESSION['user_role']`).

6. Lưu thời gian đăng nhập gần nhất
`$this->userModel->recordLastLogin((int)$user['id']);`
Giải thích:
Gọi Model cập nhật thời gian đăng nhập gần nhất (`last_login_at = NOW()`).

7. Điều hướng về trang chủ
`$this->redirect('/home');`
Giải thích:
Gọi phương thức chuyển hướng trình duyệt của người dùng sang trang chủ `/home`.""")

def add_create_employee():
    add_part("""### 👥 2. Thêm nhân viên mới (`/employees/store`)

#### Bước 1: Client gửi dữ liệu
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Admin truy cập trang Thêm nhân sự, điền các thông tin và click nút Lưu lại. Trình duyệt bắt sự kiện submit và gửi form POST chứa dữ liệu lên server."
> * **File giao diện:** [create.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/views/employees/create.php)

```html
<form method="POST" action="<?= BASE_URL ?>/employees/store" id="createForm">
    <input type="text" name="full_name" required>
    <input type="email" name="email" required>
    <input type="password" name="password" minlength="8" required>
    <button type="submit">Lưu lại</button>
</form>
```

=> giải thích: Đoạn code giao diện thêm nhân viên mới
1. Khai báo form thêm nhân viên
`<form method="POST" action="<?= BASE_URL ?>/employees/store" id="createForm">`
Giải thích:
Định nghĩa form gửi POST đến route thêm nhân sự.

2. Ô nhập họ và tên
`<input type="text" name="full_name" required>`
Giải thích:
Trường nhập Họ tên nhân viên, gửi đi dưới khóa POST `full_name`.

3. Ô nhập email
`<input type="email" name="email" required>`
Giải thích:
Trường nhập Email, bắt buộc nhập định dạng email hợp lệ.

4. Ô nhập mật khẩu khởi tạo
`<input type="password" name="password" minlength="8" required>`
Giải thích:
Trường mật khẩu, bắt buộc tối thiểu 8 ký tự phía client để đảm bảo độ bảo mật tối thiểu.

5. Nút bấm submit
`<button type="submit">Lưu lại</button>`
Giải thích:
Nút submit gửi dữ liệu lên server.

---

#### Bước 2: Controller bắt request POST
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Máy chủ tiếp nhận request và chuyển hướng tới hàm store() của EmployeeController, tại đây controller gán toàn bộ mảng POST vào biến data."
> * **File xử lý:** [EmployeeController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/EmployeeController.php) (Dòng 53-54)

```php
public function store(): void {
    $data   = $_POST;
```

=> giải thích: Hàm lưu thông tin nhân viên mới
`public function store(): void`
Hàm xử lý lưu trữ nhân viên mới. `public` có thể gọi từ bên ngoài, `void` là không trả về dữ liệu.

1. Lấy dữ liệu từ POST
`$data = $_POST;`
Giải thích:
Lưu mảng dữ liệu siêu toàn cục `$_POST` gửi lên từ form vào biến cục bộ `$data` để xử lý.

---

#### Bước 3: Validate & lấy dữ liệu
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Hệ thống gọi hàm validate để kiểm tra định dạng email, độ dài mật khẩu và kiểm tra email này đã tồn tại trong CSDL hay chưa."
> * **File xử lý:** [EmployeeController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/EmployeeController.php) (Dòng 55-65)

```php
$errors = $this->validateEmployee($data);

if (empty($errors) && $this->userModel->findByEmail($data['email'])) {
    $errors[] = 'Email này đã được sử dụng.';
}

if (!empty($errors)) {
    $this->render('employees/create', ['errors' => $errors, 'old' => $data]);
    return;
}
```

=> giải thích: Kiểm tra tính hợp lệ dữ liệu nhân viên mới
1. Gọi hàm validate
`$errors = $this->validateEmployee($data);`
Giải thích:
Gọi hàm phụ `validateEmployee()` kiểm tra họ tên không trống, email hợp lệ và mật khẩu tối thiểu 8 ký tự.

2. Kiểm tra trùng lặp Email trong database
`if (empty($errors) && $this->userModel->findByEmail($data['email'])) {`
Giải thích:
Kiểm tra trùng lặp email. Nếu không có lỗi cơ bản và email đã thuộc về một tài khoản khác trong CSDL.

3. Thêm lỗi trùng Email vào danh sách lỗi
`$errors[] = 'Email này đã được sử dụng.';`
Giải thích:
Đẩy thông báo lỗi trùng email vào danh sách lỗi.

4. Trả lỗi về giao diện nếu có lỗi
`if (!empty($errors)) { $this->render('employees/create', ['errors' => $errors, 'old' => $data]); return; }`
Giải thích:
Nếu tồn tại lỗi, gọi phương thức `$this->render()` để tải lại form thêm kèm mảng lỗi và dữ liệu đã nhập cũ (`old`), thoát hàm sớm bằng lệnh `return`.

---

#### Bước 4: Gọi Model & Tự động sinh mã nhân sự
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Nếu không có lỗi nào, Controller gọi hàm sinh mã nhân viên mới tự động (ví dụ EMP0002) và chuyển tiếp cho Model thực hiện chèn dữ liệu."
> * **File xử lý:** [EmployeeController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/EmployeeController.php) (Dòng 67-68)

```php
$data['employee_code'] = $this->userModel->generateEmployeeCode();
$newId = $this->userModel->create($data);
```

=> giải thích: Sinh mã và chèn dữ liệu nhân viên mới
1. Tự sinh mã nhân viên
`$data['employee_code'] = $this->userModel->generateEmployeeCode();`
Giải thích:
Gọi hàm sinh mã tự động từ Model, ví dụ lấy mã lớn nhất hiện tại `EMP0007` sinh ra mã tiếp theo là `EMP0008` và ghi đè vào mảng `$data`.

2. Gọi Model để lưu vào database
`$newId = $this->userModel->create($data);`
Giải thích:
Chuyển mảng dữ liệu hoàn chỉnh cho phương thức `create` của Model và nhận lại ID tự sinh (Primary Key) của bản ghi vừa chèn.

---

#### Bước 5: Model thực thi SQL
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Model nhận dữ liệu, băm mật khẩu bảo mật bằng PASSWORD_BCRYPT rồi thực thi câu lệnh SQL INSERT chèn nhân sự mới."
> * **File xử lý:** [UserModel.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/models/UserModel.php) (Dòng 174-198)

```php
public function create(array $data): int {
    $sql = "INSERT INTO users
                (email, password_hash, role, status, employee_code, full_name,
                 phone, date_of_birth, gender, department, position,
                 employment_type, base_salary, hourly_rate, start_date, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

    return $this->execute($sql, [
        $data['email'],
        password_hash($data['password'], PASSWORD_BCRYPT),
        $data['role']            ?? 'EMPLOYEE',
        $data['status']          ?? 'ACTIVE',
        $data['employee_code'],
        $data['full_name'],
        $data['phone']           ?? null,
        $data['date_of_birth']   ?? null,
        $data['gender']          ?? null,
        $data['department']      ?? null,
        $data['position']        ?? null,
        $data['employment_type'] ?? 'FULL_TIME',
        $data['base_salary']     ?? 0,
        $data['hourly_rate']     ?? 0,
        $data['start_date']      ?? null,
    ]);
}
```

=> giải thích: Lớp Model chèn dữ liệu nhân viên mới
`public function create(array $data): int`
Hàm chèn thông tin nhân viên mới vào CSDL. Nhận mảng dữ liệu `$data` và trả về số nguyên ID nhân sự vừa tạo.

1. Khai báo SQL INSERT và Placeholder
`$sql = "INSERT INTO users ... VALUES (?, ?, ...)";`
Giải thích:
Câu lệnh SQL INSERT chỉ định danh sách cột và các placeholder `?` tương ứng cho kỹ thuật SQL Parameter Binding (chống SQL Injection).

2. Thực thi SQL với băm mật khẩu và gán dữ liệu mặc định
`return $this->execute($sql, [ ... ]);`
Giải thích:
Gọi phương thức `execute` từ lớp cha `Model.php` thực thi SQL.
- `password_hash($data['password'], PASSWORD_BCRYPT)`: Mã hóa mật khẩu bảo mật một chiều bằng thuật toán BCrypt trước khi lưu.
- `?? null / ?? 0`: Toán tử kiểm tra Null Coalescing gán giá trị mặc định cho các cột tùy chọn không bắt buộc nhập.

---

#### Bước 6: Trả kết quả về Client
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Sau khi lưu thành công, Controller ghi log audit nhật ký hoạt động, tạo thông báo flash báo thành công và chuyển hướng Admin về trang danh sách nhân sự."
> * **File xử lý:** [EmployeeController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/EmployeeController.php) (Dòng 70-81)

```php
$this->sysModel->writeAudit([
    'actor_user_id'       => Auth::id(),
    'related_entity_type' => 'users',
    'related_entity_id'   => $newId,
    'action'              => 'CREATE_EMPLOYEE',
    'new_value'           => ['email' => $data['email'], 'role' => $data['role']],
    'description'         => "Admin tạo tài khoản nhân viên: {$data['full_name']}",
]);

Session::flash('success', 'Tạo nhân viên thành công.');
$this->redirect('/employees');
```

=> giải thích: Ghi log audit và chuyển hướng trang
1. Ghi log audit hoạt động hệ thống
`$this->sysModel->writeAudit([ ... ]);`
Giải thích:
Ghi nhận hoạt động vào bảng `system_records` (lưu ID Admin thực hiện qua `Auth::id()`, đối tượng tác động là `users` có ID `$newId`, hành động là `CREATE_EMPLOYEE` và lưu chuỗi JSON mô tả).

2. Tạo session thông báo thành công
`Session::flash('success', 'Tạo nhân viên thành công.');`
Giải thích:
Lưu thông báo thành công ngắn hạn hiển thị ở giao diện tiếp theo.

3. Điều hướng về trang danh sách
`$this->redirect('/employees');`
Giải thích:
Chuyển hướng người dùng về trang danh sách nhân viên `/employees`.""")

def add_update_employee():
    add_part(r"""### 📝 3. Chỉnh sửa nhân viên (`/employees/update`)

#### Bước 1: Client gửi dữ liệu
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Admin bấm vào nút sửa nhân viên, giao diện hiển thị form chứa thông tin hiện tại dưới dạng input ẩn (ID) và các input nhập liệu. Khi Admin bấm Lưu lại, sự kiện submit gửi form POST lên máy chủ."
> * **File giao diện:** [edit.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/views/employees/edit.php)

```html
<form method="POST" action="<?= BASE_URL ?>/employees/update">
    <input type="hidden" name="id" value="<?= $employee['id'] ?>">
    <input type="text" name="full_name" value="<?= htmlspecialchars($employee['full_name']) ?>" required>
    <button type="submit">Cập nhật</button>
</form>
```

=> giải thích: Đoạn code giao diện và form cập nhật thông tin
1. Khai báo form gửi thông tin cập nhật
`<form method="POST" action="<?= BASE_URL ?>/employees/update">`
Giải thích:
Thẻ `form` truyền dữ liệu chỉnh sửa bằng phương thức `POST` tới route `/employees/update`.
Ví dụ:
Khi gửi form, toàn bộ thông tin thay đổi sẽ được đóng gói gửi lên server.

2. Ô input ẩn lưu ID nhân sự
`<input type="hidden" name="id" value="<?= $employee['id'] ?>">`
Giải thích:
Input ẩn (`type="hidden"`) lưu ID của nhân viên đang được sửa. Đây là tham số bắt buộc để CSDL biết cần UPDATE bản ghi nào.
Ví dụ:
Giá trị `value="5"` cho biết đang cập nhật nhân viên có ID là 5.

3. Ô nhập họ tên điền sẵn giá trị hiện tại
`<input type="text" name="full_name" value="<?= htmlspecialchars($employee['full_name']) ?>" required>`
Giải thích:
Trường nhập Họ tên hiển thị sẵn dữ liệu cũ qua thuộc tính `value` được lọc ký tự đặc biệt bằng `htmlspecialchars` để chống lỗi bảo mật XSS.
Ví dụ:
Tên cũ "Nguyễn Văn A" sẽ hiển thị sẵn trong ô nhập để chỉnh sửa.

4. Nút bấm submit cập nhật
`<button type="submit">Cập nhật</button>`
Giải thích:
Nút submit kích hoạt sự kiện gửi dữ liệu chỉnh sửa lên máy chủ.

---

#### Bước 2: Controller bắt request POST
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Controller tiếp nhận yêu cầu cập nhật, lấy ID nhân sự từ POST, ép kiểu số nguyên và gọi Model tìm kiếm để kiểm tra tài khoản này có tồn tại hay không."
> * **File xử lý:** [EmployeeController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/EmployeeController.php) (Dòng 91-94)

```php
$id       = (int)$this->post('id');
$employee = $this->userModel->findById($id);
if (!$employee) $this->abort(404);
```

=> giải thích: Kiểm tra sự tồn tại của nhân viên cần sửa
1. Lấy ID từ POST và ép kiểu số nguyên
`$id = (int)$this->post('id');`
Giải thích:
Lấy khóa `id` trong mảng `$_POST` thông qua hàm tiện ích `post()`, sau đó dùng phép ép kiểu `(int)` để đảm bảo an toàn dữ liệu, tránh SQL injection.
Ví dụ:
Nếu truyền `id="5abc"` thì `$id` sẽ là `5`.

2. Tìm kiếm nhân sự trong database
`$employee = $this->userModel->findById($id);`
Giải thích:
Gọi phương thức `findById()` từ Model để truy vấn thông tin nhân viên theo ID.
Ví dụ:
Trả về mảng chứa toàn bộ dữ liệu nhân viên ID 5 từ bảng `users`.

3. Hủy request nếu không tìm thấy nhân sự
`if (!$employee) $this->abort(404);`
Giải thích:
Nếu biến `$employee` rỗng (không tồn tại trong CSDL), lập tức hủy bỏ request và hiển thị trang lỗi 404 Not Found.

---

#### Bước 3: Validate & lấy dữ liệu
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Hệ thống sao lưu dữ liệu cũ, nhận dữ liệu POST mới và kiểm tra xem trường họ tên có bị bỏ trống hay không. Nếu có lỗi, trả về form sửa kèm thông báo."
> * **File xử lý:** [EmployeeController.php](file:///d:/PTIT/NAM3/LTW/PHP/app/controllers/EmployeeController.php) (Dòng 96-105)

```php
$old    = $employee;
$data   = $_POST;
$errors = [];

if (empty($data['full_name'])) $errors[] = 'Họ tên không được để trống.';

if (!empty($errors)) {
    $this->render('employees/edit', ['employee' => $employee, 'errors' => $errors]);
    return;
}
```

=> giải thích: Kiểm tra tính hợp lệ của dữ liệu cập nhật
1. Lưu giữ bản sao dữ liệu cũ
`$old = $employee;`
Giải thích:
Gán thông tin nhân sự trước khi sửa vào biến `$old` nhằm phục vụ ghi nhận lịch sử thay đổi (Audit Log).

2. Nhận dữ liệu POST mới
`$data = $_POST;`
Giải thích:
Gán mảng siêu toàn cục `$_POST` vào biến cục bộ `$data`.

3. Khởi tạo mảng lỗi
`$errors = [];`
Giải thích:
Khởi tạo mảng rỗng để chứa các thông báo lỗi nếu dữ liệu nhập sai.

4. Kiểm tra họ tên trống
`if (empty($data['full_name'])) $errors[] = 'Họ tên không được để trống.';`
Giải thích:
Sử dụng hàm `empty()` kiểm tra trường họ tên gửi lên có trống không. Nếu trống, đẩy chuỗi cảnh báo vào mảng lỗi.

5. Trả lỗi và render lại form
`if (!empty($errors)) { $this->render('employees/edit', ['employee' => $employee, 'errors' => $errors]); return; }`
Giải thích:
Nếu mảng `$errors` không rỗng, gọi hàm `render()` hiển thị lại trang sửa với dữ liệu cũ và mảng lỗi, đồng thời dừng thực thi hàm.

---

#### Bước 4: Gọi Model cập nhật dữ liệu
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Nếu dữ liệu hợp lệ, Controller gọi hàm update() của UserModel truyền vào ID và mảng dữ liệu mới để cập nhật thông tin trong CSDL."
> * **File xử lý:** [EmployeeController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/EmployeeController.php) (Dòng 107)

```php
$this->userModel->update($id, $data);
```

=> giải thích: Gọi hàm cập nhật CSDL
1. Gọi phương thức Model
`$this->userModel->update($id, $data);`
Giải thích:
Controller chuyển tiếp `$id` và mảng `$data` cho phương thức `update()` của `userModel`.

---

#### Bước 5: Model thực thi SQL
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "UserModel lọc qua danh sách trường cho phép, xây dựng câu lệnh SQL UPDATE động tương ứng với dữ liệu gửi lên và thực thi qua PDO."
> * **File xử lý:** [UserModel.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/models/UserModel.php) (Dòng 200-227)

```php
public function update(int $id, array $data): bool {
    $fields = [];
    $values = [];
    $allowed = [
        'full_name', 'phone', 'date_of_birth', 'gender',
        'department', 'position', 'employment_type',
        'base_salary', 'hourly_rate', 'start_date',
    ];
    foreach ($allowed as $field) {
        if (array_key_exists($field, $data)) {
            $fields[] = "{$field} = ?";
            $values[] = $data[$field];
        }
    }
    if (empty($fields)) return false;
    $fields[] = "updated_at = NOW()";
    $values[] = $id;

    $this->execute(
        "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?",
        $values
    );
    return true;
}
```

=> giải thích: Xây dựng câu lệnh SQL UPDATE động an toàn
`public function update(int $id, array $data): bool`
Khai báo hàm cập nhật nhận ID số nguyên và mảng dữ liệu `$data`, trả về kiểu boolean (`true` / `false`).

1. Khai báo mảng chứa cột và giá trị tương ứng
`$fields = []; $values = [];`
Giải thích:
Mảng `$fields` chứa các chuỗi gán `"tên_cột = ?"`, còn `$values` chứa giá trị thực tế truyền vào các dấu chấm hỏi.

2. Khai báo danh sách trường cho phép cập nhật (Whitelisting)
`$allowed = [ 'full_name', 'phone', ... ];`
Giải thích:
Danh sách các cột an toàn được phép cập nhật để tránh việc người dùng sửa đổi các cột hệ thống (như `role`, `status`, `password_hash`).

3. Duyệt mảng lọc dữ liệu
`foreach ($allowed as $field) { if (array_key_exists($field, $data)) { ... } }`
Giải thích:
Duyệt qua danh sách `$allowed`. Nếu trường đó có trong mảng `$data`, ta thêm `"tên_cột = ?"` vào `$fields` và giá trị tương ứng vào `$values`.

4. Cập nhật thời gian thay đổi
`$fields[] = "updated_at = NOW()";`
Giải thích:
Tự động thêm trường `updated_at` bằng thời gian hiện tại của MySQL (`NOW()`).

5. Thực thi câu UPDATE động
`$this->execute("UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?", $values);`
Giải thích:
Dùng hàm `implode()` nối các phần tử của `$fields` thành chuỗi ngăn cách bởi dấu phẩy, tạo ra câu lệnh SQL UPDATE hoàn chỉnh, gán giá trị ID làm tham số cuối cùng ở mệnh đề `WHERE id = ?` và gọi phương thức `execute()`.
Ví dụ:
`UPDATE users SET full_name = ?, phone = ?, updated_at = NOW() WHERE id = ?`.

---

#### Bước 6: Trả kết quả về Client
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Sau khi sửa thành công, hệ thống ghi log audit chứa cả giá trị cũ và mới để tiện tra cứu, hiển thị thông báo flash thành công và điều hướng Admin về danh sách."
> * **File xử lý:** [EmployeeController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/EmployeeController.php) (Dòng 109-122)

```php
$this->sysModel->writeAudit([
    'actor_user_id'       => Auth::id(),
    'employee_id'         => $id,
    'related_entity_type' => 'users',
    'related_entity_id'   => $id,
    'action'              => 'UPDATE_EMPLOYEE',
    'old_value'           => $old,
    'new_value'           => $data,
    'description'         => "Admin cập nhật hồ sơ nhân viên ID {$id}",
]);

Session::flash('success', 'Cập nhật thành công.');
$this->redirect('/employees');
```

=> giải thích: Ghi nhật ký chỉnh sửa và điều hướng
1. Ghi nhật ký Audit Log
`$this->sysModel->writeAudit([ ... ]);`
Giải thích:
Lưu vết hoạt động gồm ID người thực hiện (`Auth::id()`), ID đối tượng chịu tác động (`$id`), hành động (`UPDATE_EMPLOYEE`), giá trị cũ (`$old`), giá trị mới (`$data`) phục vụ bảo mật thông tin.

2. Tạo thông báo flash thành công
`Session::flash('success', 'Cập nhật thành công.');`
Giải thích:
Thiết lập session flash để hiển thị thông báo thành công ở trang tiếp theo.

3. Điều hướng về danh sách nhân viên
`$this->redirect('/employees');`
Giải thích:
Chuyển hướng trình duyệt của Admin về route `/employees`.""")

def add_delete_employee():
    add_part(r"""### 🗑️ 4. Xóa mềm nhân viên (`/employees/delete`)

#### Bước 1: Client gửi dữ liệu
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Admin bấm nút Xóa nhân sự, trình duyệt yêu cầu xác nhận xác thực confirm. Nếu đồng ý, form POST chứa ID nhân viên được gửi ẩn lên máy chủ."
> * **File giao diện:** [index.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/views/employees/index.php)

```html
<form method="POST" action="<?= BASE_URL ?>/employees/delete" onsubmit="return confirm('Bạn có chắc chắn muốn xóa?');" style="display:inline;">
    <input type="hidden" name="id" value="<?= $emp['id'] ?>">
    <button type="submit">Xóa</button>
</form>
```

=> giải thích: Form gửi yêu cầu xóa nhân viên
1. Khai báo form POST xóa nhân sự
`<form method="POST" action="<?= BASE_URL ?>/employees/delete" onsubmit="return confirm('Bạn có chắc chắn muốn xóa?');" style="display:inline;">`
Giải thích:
Form gửi yêu cầu xóa qua phương thức `POST` tới route `/employees/delete`. Sự kiện `onsubmit` gọi hộp thoại xác nhận `confirm()` phía client để tránh việc Admin ấn nhầm nút.
Ví dụ:
Khi Admin click nút Xóa, trình duyệt hiện hộp thoại hỏi "Bạn có chắc chắn muốn xóa?". Nếu chọn Cancel thì form không gửi đi.

2. Ô input ẩn truyền ID cần xóa
`<input type="hidden" name="id" value="<?= $emp['id'] ?>">`
Giải thích:
Input ẩn truyền giá trị ID của nhân viên cần xóa.
Ví dụ:
`value="3"` cho biết sẽ yêu cầu xóa nhân viên có ID bằng 3.

3. Nút bấm kích hoạt xóa
`<button type="submit">Xóa</button>`
Giải thích:
Nút submit gửi yêu cầu xóa.

---

#### Bước 2: Controller bắt request POST
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Máy chủ tiếp nhận request POST, Controller thực hiện lấy ID từ mảng POST và ép kiểu số nguyên để xử lý."
> * **File xử lý:** [EmployeeController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/EmployeeController.php) (Dòng 147-148)

```php
$id = (int)$this->post('id');
```

=> giải thích: Tiếp nhận ID cần xóa
1. Lấy ID từ POST
`$id = (int)$this->post('id');`
Giải thích:
Lọc dữ liệu POST lấy trường `id` và ép sang kiểu số nguyên để bảo mật.

---

#### Bước 3: Validate quyền hạn & an toàn
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Hệ thống kiểm tra ngăn cấm Admin tự xóa tài khoản của chính mình. Sau đó tìm kiếm nhân sự trong CSDL, nếu không tồn tại thì báo lỗi 404."
> * **File xử lý:** [EmployeeController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/EmployeeController.php) (Dòng 150-157)

```php
if ($id === Auth::id()) {
    Session::flash('error', 'Bạn không thể tự xóa tài khoản của chính mình.');
    $this->redirect('/employees');
    return;
}

$employee = $this->userModel->findById($id);
if (!$employee) $this->abort(404);
```

=> giải thích: Kiểm tra an toàn trước khi xóa
1. Ngăn chặn tự xóa bản thân
`if ($id === Auth::id()) { ... }`
Giải thích:
So sánh ID cần xóa với ID của Admin đang đăng nhập thông qua `Auth::id()`. Nếu trùng khớp, thiết lập flash thông báo lỗi và điều hướng ngược lại, dừng hàm.
Ví dụ:
Nếu Admin ID là 1 cố tình gửi request xóa ID 1, hệ thống sẽ ngăn cấm.

2. Tìm kiếm nhân viên cần xóa
`$employee = $this->userModel->findById($id);`
Giải thích:
Gọi Model tìm kiếm nhân sự xem có tồn tại trong CSDL hay không.

3. Hủy request nếu không tìm thấy
`if (!$employee) $this->abort(404);`
Giải thích:
Nếu không tìm thấy nhân sự tương ứng với ID, trả về trang lỗi 404.

---

#### Bước 4: Gọi Model thực hiện xóa mềm
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Khi kiểm tra an toàn thành công, Controller gọi phương thức softDelete() của UserModel để tiến hành xóa mềm nhân viên."
> * **File xử lý:** [EmployeeController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/EmployeeController.php) (Dòng 159)

```php
$this->userModel->softDelete($id);
```

=> giải thích: Gọi hàm xóa mềm của Model
1. Gọi phương thức Model
`$this->userModel->softDelete($id);`
Giải thích:
Chuyển tiếp yêu cầu xóa mềm nhân sự ID đó tới lớp Model.

---

#### Bước 5: Model thực thi SQL
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "UserModel chạy lệnh SQL UPDATE ghi nhận thời gian xóa vào trường deleted_at thay vì dùng câu lệnh DELETE cứng, đảm bảo an toàn và bảo toàn dữ liệu."
> * **File xử lý:** [UserModel.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/models/UserModel.php) (Dòng 243-248)

```php
public function softDelete(int $id): void {
    $this->execute(
        "UPDATE users SET deleted_at = NOW(), updated_at = NOW() WHERE id = ?",
        [$id]
    );
}
```

=> giải thích: Thực thi SQL cập nhật trường xóa mềm
`public function softDelete(int $id): void`
Khai báo hàm xóa mềm nhận tham số ID số nguyên, không trả về giá trị.

1. Chạy câu lệnh SQL UPDATE thay vì DELETE
`$this->execute("UPDATE users SET deleted_at = NOW(), updated_at = NOW() WHERE id = ?", [$id]);`
Giải thích:
Chạy câu lệnh SQL cập nhật cột `deleted_at` thành thời gian hiện tại (`NOW()`). Các câu truy vấn lấy danh sách bình thường đều có điều kiện `WHERE deleted_at IS NULL` nên nhân viên này sẽ biến mất khỏi hệ thống nhưng dữ liệu lịch sử liên quan (như chấm công, bảng lương) không bị mất mát.
Ví dụ:
Khi truy vấn SQL, bản ghi có ID bằng 3 sẽ có giá trị cột `deleted_at = '2026-05-22 00:55:00'`.

---

#### Bước 6: Trả kết quả về Client
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Sau khi xóa thành công, hệ thống ghi nhật ký hoạt động Audit Log, gửi thông báo flash báo thành công và chuyển hướng Admin về trang danh sách nhân sự."
> * **File xử lý:** [EmployeeController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/EmployeeController.php) (Dòng 161-172)

```php
$this->sysModel->writeAudit([
    'actor_user_id'       => Auth::id(),
    'employee_id'         => $id,
    'related_entity_type' => 'users',
    'related_entity_id'   => $id,
    'action'              => 'DELETE_EMPLOYEE',
    'old_value'           => $employee,
    'description'         => "Admin xóa nhân viên ID {$id} ({$employee['full_name']})",
]);

Session::flash('success', "Đã xóa nhân viên {$employee['full_name']} thành công.");
$this->redirect('/employees');
```

=> giải thích: Ghi nhật ký xóa nhân viên và điều hướng
1. Ghi Audit Log hoạt động xóa
`$this->sysModel->writeAudit([ ... ]);`
Giải thích:
Ghi lại vết xóa nhân viên gồm người xóa, đối tượng bị xóa và toàn bộ snapshot dữ liệu cũ (`$employee`) để phục hồi nếu cần thiết.

2. Đặt thông báo thành công
`Session::flash('success', "Đã xóa nhân viên {$employee['full_name']} thành công.");`
Giải thích:
Tạo session flash thông báo xóa thành công.

3. Điều hướng về trang danh sách
`$this->redirect('/employees');`
Giải thích:
Chuyển hướng trình duyệt về route `/employees`.""")

def add_search_employee():
    add_part(r"""### 🔍 5. Tìm kiếm nhân viên realtime bằng AJAX (`/ajax/employees/search`)

#### Bước 1: Client gửi dữ liệu (AJAX request)
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Khi Admin gõ chữ vào thanh tìm kiếm hoặc thay đổi bộ lọc, JavaScript lắng nghe sự kiện keyup/change, thu thập giá trị và thực hiện gửi request GET không tải lại trang tới máy chủ."
> * **File giao diện:** [index.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/views/employees/index.php)

```javascript
$('#q, #department, #role, #status').on('keyup change', function() {
    let q    = $('#q').val();
    let dept = $('#department').val();
    let role = $('#role').val();
    let stat = $('#status').val();

    $.ajax({
        url: BASE_URL + '/ajax/employees/search',
        method: 'GET',
        data: { q: q, department: dept, role: role, status: stat },
        success: function(res) {
            if (res.success) {
                // Xóa bảng cũ và render dòng mới từ res.data
            }
        }
    });
});
```

=> giải thích: JavaScript gửi yêu cầu tìm kiếm không tải lại trang
1. Lắng nghe sự kiện bàn phím và thay đổi bộ lọc
`$('#q, #department, #role, #status').on('keyup change', function() { ... })`
Giải thích:
Bắt sự kiện gõ phím (`keyup`) trên ô tìm kiếm `#q` hoặc thay đổi lựa chọn (`change`) ở các ô select phòng ban, vai trò, trạng thái.
Ví dụ:
Admin gõ chữ "An" vào ô tìm kiếm, sự kiện keyup lập tức được kích hoạt.

2. Thu thập dữ liệu các trường bộ lọc
`let q = $('#q').val(); ...`
Giải thích:
Lấy giá trị hiện tại của các phần tử input và select gán vào các biến tương ứng.

3. Gửi Ajax GET lên máy chủ
`$.ajax({ url: BASE_URL + '/ajax/employees/search', method: 'GET', data: { ... } })`
Giải thích:
Gọi phương thức Ajax của jQuery gửi HTTP GET Request tới endpoint `/ajax/employees/search` kèm theo các tham số tìm kiếm trong đối tượng `data`.

---

#### Bước 2: Controller bắt request GET
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Controller tiếp nhận request tại hàm ajaxSearch(), lập tức khai báo Content-Type dạng application/json để trình duyệt hiểu kết quả trả về là chuỗi dữ liệu JSON."
> * **File xử lý:** [EmployeeController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/EmployeeController.php) (Dòng 178-179)

```php
public function ajaxSearch(): void {
    header('Content-Type: application/json; charset=utf-8');
```

=> giải thích: Cấu hình Header phản hồi JSON
`public function ajaxSearch(): void`
Khai báo hàm xử lý tìm kiếm AJAX không trả về dữ liệu thuần.

1. Khai báo Header kiểu JSON
`header('Content-Type: application/json; charset=utf-8');`
Giải thích:
Gửi header định dạng dữ liệu trả về cho trình duyệt là JSON với bảng mã ký tự utf-8 để không bị lỗi hiển thị tiếng Việt.

---

#### Bước 3: Lấy dữ liệu & Làm sạch
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Hệ thống lấy các tham số q, department, role, status từ GET, cắt bỏ khoảng trắng thừa hai đầu và đóng gói vào mảng filters."
> * **File xử lý:** [EmployeeController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/EmployeeController.php) (Dòng 180-185)

```php
$q          = trim($this->get('q', ''));
$department = trim($this->get('department', ''));
$role       = trim($this->get('role', ''));
$status     = trim($this->get('status', ''));

$filters = compact('q', 'department', 'role', 'status');
```

=> giải thích: Nhận tham số tìm kiếm
1. Lấy dữ liệu tìm kiếm từ GET
`$q = trim($this->get('q', '')); ...`
Giải thích:
Gọi phương thức `get()` từ lớp cha `Controller.php` để lấy giá trị từ mảng `$_GET`, dùng `trim()` cắt bỏ khoảng trắng thừa hai đầu.
Ví dụ:
Nếu Admin nhập `" An "`, biến `$q` nhận giá trị `"An"`.

2. Đóng gói bộ lọc thành mảng
`$filters = compact('q', 'department', 'role', 'status');`
Giải thích:
Hàm `compact()` trong PHP tự động tạo một mảng kết hợp từ các biến có tên trùng khớp với tham số truyền vào.
Ví dụ:
`['q' => 'An', 'department' => 'IT', 'role' => '', 'status' => '']`.

---

#### Bước 4: Gọi Model truy vấn danh sách lọc
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Controller gọi phương thức findFiltered() của Model, chuyển qua mảng filters và chỉ định số bản ghi tối đa lấy ra là 50 dòng."
> * **File xử lý:** [EmployeeController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/EmployeeController.php) (Dòng 186)

```php
$employees = $this->userModel->findFiltered($filters, 1, 50);
```

=> giải thích: Gọi Model truy vấn dữ liệu lọc
1. Gọi hàm Model lọc danh sách nhân sự
`$employees = $this->userModel->findFiltered($filters, 1, 50);`
Giải thích:
Gọi phương thức lọc từ Model, chỉ định lấy trang 1 (`1`) và lấy tối đa 50 nhân viên (`50`) khớp bộ lọc.

---

#### Bước 5: Model thực thi SQL
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Model kiểm tra từng biến lọc. Nếu tồn tại giá trị, tiến hành nối chuỗi SQL động tương ứng và sử dụng toán tử LIKE % từ khóa % để tìm kiếm tương đối một cách an toàn."
> * **File xử lý:** [UserModel.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/models/UserModel.php) (Dòng 46-83)

```php
public function findFiltered(array $filters, int $page = 1, int $perPage = 20): array {
    $offset = ($page - 1) * $perPage;
    $sql = "SELECT id, employee_code, full_name, email, role, status, department,
                   position, employment_type, start_date, created_at
            FROM users
            WHERE deleted_at IS NULL";
    $params = [];
    if (!empty($filters['q'])) {
        $sql .= " AND (full_name LIKE ? OR email LIKE ? OR employee_code LIKE ?)";
        $like = "%{$filters['q']}%";
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }
    if (!empty($filters['department'])) {
        $sql .= " AND department = ?";
        $params[] = $filters['department'];
    }
    $sql .= " ORDER BY full_name LIMIT ? OFFSET ?";
    $params[] = $perPage;
    $params[] = $offset;

    return $this->query($sql, $params);
}
```

=> giải thích: Hàm truy vấn SQL động an toàn phòng chống SQL Injection
`public function findFiltered(array $filters, int $page = 1, int $perPage = 20): array`
Khai báo hàm lọc nhận mảng filters, trang hiện tại, số bản ghi một trang, trả về mảng kết quả.

1. Tính toán offset phân trang
`$offset = ($page - 1) * $perPage;`
Giải thích:
Xác định vị trí bắt đầu lấy bản ghi trong CSDL.
Ví dụ:
Trang 1 thì offset = 0. Trang 2 offset = 20.

2. Khởi tạo câu SQL cơ bản và mảng tham số
`$sql = "SELECT ... WHERE deleted_at IS NULL"; $params = [];`
Giải thích:
SQL mặc định lấy các tài khoản chưa bị xóa mềm, mảng `$params` dùng để liên kết giá trị thực tế sau này.

3. Nối chuỗi SQL tìm kiếm theo từ khóa
`if (!empty($filters['q'])) { ... }`
Giải thích:
Nếu Admin gõ từ khóa tìm kiếm, ta thêm điều kiện tìm tương đối bằng `LIKE ?` trên 3 cột họ tên, email hoặc mã nhân viên. Dữ liệu gán vào mảng `$params` được bọc bởi dấu phần trăm `%`.
Ví dụ:
Nếu `$filters['q']` là `"An"`, `$like` sẽ là `"%An%"`.

4. Nối chuỗi SQL lọc theo phòng ban
`if (!empty($filters['department'])) { ... }`
Giải thích:
Nếu chọn phòng ban cụ thể, thêm điều kiện so khớp bằng `AND department = ?` và đẩy phòng ban vào mảng tham số.

5. Nối giới hạn phân trang và thực thi
`$sql .= " ORDER BY full_name LIMIT ? OFFSET ?";`
Giải thích:
Sắp xếp theo họ tên, giới hạn số lượng và vị trí lấy, thực thi câu lệnh SQL hoàn chỉnh qua phương thức `query()`.

---

#### Bước 6: Trả kết quả JSON về Client
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Sau khi lấy danh sách nhân viên từ Model, Controller duyệt qua chuyển đổi dữ liệu sang định dạng hiển thị đẹp, tính toán ký tự viết tắt tên rồi mã hóa trả về chuỗi JSON kết thúc xử lý."
> * **File xử lý:** [EmployeeController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/EmployeeController.php) (Dòng 188-208)

```php
$roleMap   = ['ADMIN' => 'Quản trị viên', 'EMPLOYEE' => 'Nhân viên'];
$statusMap = ['ACTIVE' => 'Hoạt động', 'LOCKED' => 'Tạm khóa'];

$result = array_map(function ($emp) use ($roleMap, $statusMap) {
    return [
        'id'         => $emp['id'],
        'full_name'  => $emp['full_name'],
        'email'      => $emp['email'],
        'role'       => $emp['role'],
        'role_label' => $roleMap[$emp['role']] ?? $emp['role'],
        'status'     => $emp['status'],
        'status_label' => $statusMap[$emp['status']] ?? $emp['status'],
        'department' => $emp['department'] ?? '',
        'position'   => $emp['position'] ?? '',
        'initials'   => mb_strtoupper(mb_substr($emp['full_name'], 0, 1)),
    ];
}, $employees);

echo json_encode(['success' => true, 'data' => $result, 'total' => count($result)]);
exit;
```

=> giải thích: Chuyển đổi dữ liệu và xuất phản hồi JSON
1. Khai báo bản đồ ánh xạ nhãn hiển thị tiếng Việt
`$roleMap = [...]; $statusMap = [...];`
Giải thích:
Dùng để dịch các giá trị lưu trữ CSDL (như `ADMIN`, `ACTIVE`) sang tiếng Việt hiển thị trên giao diện ("Quản trị viên", "Hoạt động").

2. Duyệt ánh xạ dữ liệu đầu ra đẹp
`$result = array_map(function ($emp) use ($roleMap, $statusMap) { ... }, $employees);`
Giải thích:
Sử dụng hàm `array_map()` duyệt qua từng dòng của mảng `$employees` để định dạng lại các trường, đồng thời dùng `mb_substr` lấy chữ cái đầu tiên của Họ tên làm ký tự đại diện avatar (`initials`).
Ví dụ:
"Nguyễn Văn An" sẽ có `initials` là "N".

3. Mã hóa JSON và gửi phản hồi
`echo json_encode(['success' => true, 'data' => $result, 'total' => count($result)]); exit;`
Giải thích:
Mã hóa mảng kết quả sang định dạng chuỗi JSON bằng hàm `json_encode()` rồi dùng lệnh `echo` xuất ra, kết thúc chạy mã bằng lệnh `exit` để không bị thừa ký tự trống phía sau CSDL.
Ví dụ:
`{"success":true,"data":[{"id":5,"full_name":"Nguyễn Văn An",...}],"total":1}`.""")

def add_toggle_status():
    add_part(r"""### 🔒 6. Khóa/Mở khóa tài khoản bằng AJAX (`/ajax/employees/toggle-status`)

#### Bước 1: Client gửi dữ liệu (AJAX request)
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Admin click nút khóa tài khoản, JavaScript chặn hành động reload trang, thu thập ID nhân sự và gửi một request POST ẩn lên máy chủ bằng phương thức AJAX."
> * **File giao diện:** [index.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/views/employees/index.php)

```javascript
$('.toggle-status-btn').on('click', function() {
    let empId = $(this).data('id');
    $.ajax({
        url: BASE_URL + '/ajax/employees/toggle-status',
        method: 'POST',
        data: { id: empId },
        success: function(res) {
            if (res.success) {
                // Thay đổi biểu tượng khóa/mở và màu sắc dòng tương ứng
            }
        }
    });
});
```

=> giải thích: JavaScript gửi yêu cầu khóa/mở tài khoản không tải lại trang
1. Gắn sự kiện click vào nút đổi trạng thái
`$('.toggle-status-btn').on('click', function() { ... })`
Giải thích:
Tìm các class `.toggle-status-btn` và lắng nghe sự kiện click chuột.
Ví dụ:
Admin click chuột vào nút biểu tượng ổ khóa của nhân viên Nguyễn Văn A.

2. Lấy ID từ thuộc tính dữ liệu HTML
`let empId = $(this).data('id');`
Giải thích:
Lấy giá trị của thuộc tính `data-id` trên thẻ HTML vừa được click.
Ví dụ:
`<button class="toggle-status-btn" data-id="12">` sẽ lấy ra được giá trị `empId = 12`.

3. Gửi Ajax POST
`$.ajax({ url: BASE_URL + '/ajax/employees/toggle-status', method: 'POST', data: { id: empId } })`
Giải thích:
Gửi request dạng `POST` tới đường dẫn xử lý Ajax kèm tham số ID nhân viên.

---

#### Bước 2: Controller bắt request POST
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Hệ thống gọi hàm ajaxToggleStatus(), khai báo dữ liệu trả về dạng JSON và lấy ID từ POST, ép kiểu số nguyên để xử lý."
> * **File xử lý:** [EmployeeController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/EmployeeController.php) (Dòng 213-215)

```php
public function ajaxToggleStatus(): void {
    header('Content-Type: application/json; charset=utf-8');
    $id = (int)$this->post('id');
```

=> giải thích: Khởi tạo AJAX xử lý trạng thái
1. Đặt định dạng dữ liệu trả về là JSON
`header('Content-Type: application/json; charset=utf-8');`
Giải thích:
Khai báo kiểu nội dung đầu ra là JSON để client nhận diện và phân tích cú pháp dễ dàng.

2. Tiếp nhận ID từ POST
`$id = (int)$this->post('id');`
Giải thích:
Lấy giá trị ID trong POST và ép kiểu số nguyên.

---

#### Bước 3: Validate tính an toàn
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Controller kiểm tra xem Admin có đang tự khóa tài khoản của chính mình hay không (nếu có thì từ chối). Tiếp theo tìm nhân sự trong DB, nếu không có thì trả lỗi."
> * **File xử lý:** [EmployeeController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/EmployeeController.php) (Dòng 217-226)

```php
if ($id === Auth::id()) {
    echo json_encode(['success' => false, 'message' => 'Không thể tự khóa tài khoản của chính mình.']);
    exit;
}

$employee = $this->userModel->findById($id);
if (!$employee) {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy nhân viên.']);
    exit;
}
```

=> giải thích: Kiểm tra ràng buộc an toàn tài khoản
1. Ngăn tự khóa bản thân
`if ($id === Auth::id()) { ... }`
Giải thích:
So sánh ID gửi lên khớp với ID của Admin đang thao tác (`Auth::id()`), xuất ra JSON báo thất bại kèm thông báo lỗi và thoát sớm bằng lệnh `exit`.

2. Tìm kiếm nhân viên cần chuyển trạng thái
`$employee = $this->userModel->findById($id);`
Giải thích:
Gọi Model lấy thông tin nhân viên theo ID.

3. Kiểm tra sự tồn tại của nhân sự
`if (!$employee) { ... }`
Giải thích:
Nếu biến `$employee` rỗng (không tồn tại trong CSDL), xuất JSON lỗi và dừng chương trình.

---

#### Bước 4: Gọi Model đổi trạng thái
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Nếu mọi kiểm tra hợp lệ, Controller tiến hành đảo ngược trạng thái tài khoản hiện tại (ACTIVE thành LOCKED và ngược lại) rồi gọi Model cập nhật."
> * **File xử lý:** [EmployeeController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/EmployeeController.php) (Dòng 228-229)

```php
$newStatus = $employee['status'] === 'ACTIVE' ? 'LOCKED' : 'ACTIVE';
$this->userModel->updateStatus($id, $newStatus);
```

=> giải thích: Thực hiện đảo trạng thái tài khoản
1. Xác định trạng thái mới
`$newStatus = $employee['status'] === 'ACTIVE' ? 'LOCKED' : 'ACTIVE';`
Giải thích:
Sử dụng toán tử ba ngôi kiểm tra trạng thái hiện tại. Nếu trạng thái đang là `'ACTIVE'` thì đổi thành `'LOCKED'`, ngược lại thì đổi thành `'ACTIVE'`.
Ví dụ:
Nhân viên đang Hoạt động (`ACTIVE`) sẽ bị chuyển thành Tạm khóa (`LOCKED`).

2. Gọi Model cập nhật trạng thái mới
`$this->userModel->updateStatus($id, $newStatus);`
Giải thích:
Gọi phương thức `updateStatus()` của Model để ghi nhận trạng thái mới vào CSDL.

---

#### Bước 5: Model thực thi SQL
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "UserModel thực thi câu lệnh SQL UPDATE cập nhật trực tiếp cột status của tài khoản nhân sự có ID chỉ định trong bảng users."
> * **File xử lý:** [UserModel.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/models/UserModel.php) (Dòng 229-234)

```php
public function updateStatus(int $id, string $status): void {
    $this->execute(
        "UPDATE users SET status = ?, updated_at = NOW() WHERE id = ?",
        [$status, $id]
    );
}
```

=> giải thích: SQL cập nhật cột trạng thái tài khoản
`public function updateStatus(int $id, string $status): void`
Khai báo hàm cập nhật trạng thái nhận ID và chuỗi trạng thái mới.

1. Thực thi UPDATE trạng thái tài khoản
`$this->execute("UPDATE users SET status = ?, updated_at = NOW() WHERE id = ?", [$status, $id]);`
Giải thích:
Chạy lệnh SQL cập nhật giá trị cột `status` và `updated_at` trong bảng `users` lọc theo điều kiện ID.

---

#### Bước 6: Ghi nhật ký & Trả kết quả JSON
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Sau khi lưu CSDL, hệ thống ghi nhật ký hoạt động thay đổi trạng thái, dịch trạng thái mới sang tiếng Việt hiển thị và mã hóa trả về JSON."
> * **File xử lý:** [EmployeeController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/EmployeeController.php) (Dòng 231-244)

```php
$this->sysModel->writeAudit([
    'actor_user_id'       => Auth::id(),
    'employee_id'         => $id,
    'related_entity_type' => 'users',
    'related_entity_id'   => $id,
    'action'              => "SET_STATUS_{$newStatus}",
    'old_value'           => ['status' => $employee['status']],
    'new_value'           => ['status' => $newStatus],
    'description'         => "Admin thay đổi trạng thái tài khoản ID {$id} -> {$newStatus}",
]);

$label = $newStatus === 'LOCKED' ? 'Tạm khóa' : 'Hoạt động';
echo json_encode(['success' => true, 'new_status' => $newStatus, 'label' => $label]);
exit;
```

=> giải thích: Ghi log Audit và trả JSON thành công
1. Ghi nhận nhật ký thay đổi trạng thái
`$this->sysModel->writeAudit([ ... ]);`
Giải thích:
Ghi vết hoạt động cập nhật trạng thái của Admin trên tài khoản nhân sự.

2. Chuyển trạng thái sang nhãn tiếng Việt hiển thị
`$label = $newStatus === 'LOCKED' ? 'Tạm khóa' : 'Hoạt động';`
Giải thích:
Gán chuỗi hiển thị tiếng Việt tương ứng trạng thái mới để cập nhật giao diện mà không cần reload trang.

3. Trả về JSON thành công
`echo json_encode(['success' => true, 'new_status' => $newStatus, 'label' => $label]); exit;`
Giải thích:
Mã hóa JSON trả thông tin trạng thái mới về cho trình duyệt xử lý hiển thị trực quan và thoát chương trình.""")

def add_create_leave():
    add_part(r"""### 📅 7. Đăng ký nghỉ phép (`/leave/store`)

#### Bước 1: Client gửi dữ liệu
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Nhân viên truy cập trang xin nghỉ phép, chọn ngày bắt đầu, ngày kết thúc, loại nghỉ phép (nghỉ ốm, nghỉ phép năm, việc riêng) và gõ lý do nghỉ rồi ấn nút gửi form POST."
> * **File giao diện:** [create.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/views/leave/create.php)

```html
<form method="POST" action="<?= BASE_URL ?>/leave/store">
    <input type="date" name="start_date" required>
    <input type="date" name="end_date" required>
    <select name="leave_type" required>
        <option value="ANNUAL">Nghỉ phép năm</option>
        <option value="SICK">Nghỉ ốm</option>
        <option value="UNPAID">Nghỉ không lương</option>
    </select>
    <textarea name="reason" required></textarea>
    <button type="submit">Gửi đơn</button>
</form>
```

=> giải thích: Form giao diện đăng ký nghỉ phép của nhân viên
1. Khai báo form đăng ký nghỉ phép
`<form method="POST" action="<?= BASE_URL ?>/leave/store">`
Giải thích:
Form gửi yêu cầu tạo đơn nghỉ phép bằng phương thức `POST` tới route `/leave/store`.

2. Các ô nhập ngày nghỉ phép
`<input type="date" name="start_date" required> ...`
Giải thích:
Các ô chọn ngày bắt đầu và kết thúc nghỉ phép dưới dạng lịch chọn (`type="date"`).

3. Lựa chọn loại nghỉ phép
`<select name="leave_type" required> ...`
Giải thích:
Hộp thoại select cho phép nhân viên chọn loại hình nghỉ phép phù hợp.

4. Nhập lý do nghỉ phép
`<textarea name="reason" required></textarea>`
Giải thích:
Khung nhập văn bản đa dòng để nhân viên trình bày lý do nghỉ cụ thể.

---

#### Bước 2: Controller bắt request POST
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Controller bắt request, lấy ID người đăng nhập thông qua Auth::id() và gán các trường từ form vào các biến cục bộ tương ứng."
> * **File xử lý:** [LeaveController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/LeaveController.php) (Dòng 49-55)

```php
$employeeId = Auth::id();
$startDate  = $this->post('start_date');
$endDate    = $this->post('end_date');
$leaveType  = $this->post('leave_type');
$reason     = trim($this->post('reason', ''));
$errors     = [];
```

=> giải thích: Tiếp nhận dữ liệu đơn xin nghỉ phép
1. Xác định ID nhân viên gửi đơn nghỉ
`$employeeId = Auth::id();`
Giải thích:
Lấy ID của tài khoản nhân viên đang đăng nhập thông qua hàm tĩnh `Auth::id()`.
Ví dụ:
Nếu nhân viên Nguyễn Văn A có ID là 4 đang đăng nhập, `$employeeId` sẽ là 4.

2. Lấy dữ liệu các trường từ POST
`$startDate = $this->post('start_date'); ...`
Giải thích:
Nhận giá trị các trường ngày bắt đầu, ngày kết thúc, loại nghỉ, lý do nghỉ từ mảng POST.

---

#### Bước 3: Validate logic thời gian & trùng lặp
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Hệ thống kiểm tra rỗng các trường bắt buộc, validate logic ngày bắt đầu phải trước ngày kết thúc, lý do từ 5 ký tự và gọi Model kiểm tra xem nhân viên có đơn nghỉ nào trùng lặp thời gian này hay chưa."
> * **File xử lý:** [LeaveController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/LeaveController.php) (Dòng 57-72)

```php
if (!$startDate || !$endDate)          $errors[] = 'Vui lòng chọn ngày bắt đầu và kết thúc.';
if ($startDate > $endDate)             $errors[] = 'Ngày bắt đầu phải trước hoặc bằng ngày kết thúc.';
if (empty($leaveType))                 $errors[] = 'Vui lòng chọn loại nghỉ phép.';
if (strlen($reason) < 5)               $errors[] = 'Lý do nghỉ phải ít nhất 5 ký tự.';

if (empty($errors) && $this->leaveModel->hasOverlap($employeeId, $startDate, $endDate)) {
    $errors[] = 'Bạn đã có đơn nghỉ trùng khoảng thời gian này.';
}

if (!empty($errors)) {
    $this->render('leave/create', [
        'errors' => $errors,
        'old'    => $_POST,
    ]);
    return;
}
```

=> giải thích: Kiểm tra tính hợp lệ và trùng lặp thời gian nghỉ phép
1. Kiểm tra điền đủ ngày nghỉ
`if (!$startDate || !$endDate) $errors[] = 'Vui lòng chọn ngày bắt đầu và kết thúc.';`
Giải thích:
Đảm bảo Admin/Nhân viên phải chọn đủ cả hai mốc ngày.

2. So sánh logic mốc thời gian
`if ($startDate > $endDate) $errors[] = 'Ngày bắt đầu phải trước hoặc bằng ngày kết thúc.';`
Giải thích:
Tránh lỗi logic thời gian khi ngày bắt đầu lại sau ngày kết thúc.
Ví dụ:
Nếu chọn ngày bắt đầu là `2026-05-25` và ngày kết thúc `2026-05-20` thì mảng lỗi nhận thông báo lỗi.

3. Kiểm tra độ dài lý do
`if (strlen($reason) < 5) $errors[] = 'Lý do nghỉ phải ít nhất 5 ký tự.';`
Giải thích:
Bắt buộc nhập lý do nghỉ rõ ràng tối thiểu 5 ký tự.

4. Gọi Model kiểm tra đơn trùng thời gian
`if (empty($errors) && $this->leaveModel->hasOverlap($employeeId, $startDate, $endDate)) { ... }`
Giải thích:
Nếu không có lỗi nhập liệu cơ bản, gọi phương thức `hasOverlap()` kiểm tra xem trong khoảng thời gian xin nghỉ này đã có đơn nào được Duyệt hoặc Đang chờ duyệt trước đó chưa. Nếu trùng, báo lỗi.

5. Trả lỗi và render lại form
`if (!empty($errors)) { ... return; }`
Giải thích:
Nếu phát hiện lỗi, hiển thị lại form kèm dữ liệu cũ `old` đã điền và mảng lỗi để người dùng sửa lại, dừng hàm.

---

#### Bước 4: Gọi Model thêm đơn nghỉ phép mới
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Nếu không xảy ra lỗi, Controller gọi hàm create() của LeaveRequestModel truyền vào mảng thông tin để tạo đơn nghỉ phép."
> * **File xử lý:** [LeaveController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/LeaveController.php) (Dòng 74-80)

```php
$this->leaveModel->create([
    'employee_id' => $employeeId,
    'leave_type'  => $leaveType,
    'start_date'  => $startDate,
    'end_date'    => $endDate,
    'reason'      => $reason,
]);
```

=> giải thích: Gọi Model ghi nhận đơn nghỉ phép
1. Gọi phương thức Model
`$this->leaveModel->create([ ... ]);`
Giải thích:
Chuyển tiếp mảng tham số sang Model để thực hiện lưu trữ thông tin đơn nghỉ mới.

---

#### Bước 5: Model thực thi SQL & Check overlap
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "LeaveRequestModel chạy SQL SELECT kiểm tra trùng khoảng ngày nghỉ ở hàm hasOverlap() trước. Sau đó chạy câu lệnh SQL INSERT chèn đơn nghỉ phép với trạng thái mặc định là PENDING."
> * **File xử lý:** [LeaveRequestModel.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/models/LeaveRequestModel.php) (Dòng 9-22, Dòng 71-87)

```php
public function hasOverlap(int $employeeId, string $startDate, string $endDate, ?int $excludeId = null): bool {
    $excludeSql = $excludeId ? 'AND id != ?' : '';
    $params     = [$employeeId, $startDate, $endDate, $startDate, $endDate];
    if ($excludeId) $params[] = $excludeId;

    $row = $this->queryOne(
        "SELECT id FROM leave_requests
         WHERE employee_id = ?
           AND status IN ('PENDING','APPROVED')
           AND start_date <= ?
           AND end_date   >= ?
           {$excludeSql}
         LIMIT 1",
        $params
    );
    return $row !== null;
}

public function create(array $data): int {
    return $this->execute(
        "INSERT INTO leave_requests
            (employee_id, leave_type, start_date, end_date, reason, status, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, 'PENDING', NOW(), NOW())",
        [
            $data['employee_id'],
            $data['leave_type'],
            $data['start_date'],
            $data['end_date'],
            $data['reason'],
        ]
    );
}
```

=> giải thích: SQL kiểm tra overlap và chèn đơn nghỉ phép mới
`public function hasOverlap(...)`
Khai báo hàm kiểm tra trùng lịch xin nghỉ của một nhân viên, trả về boolean.

1. Truy vấn kiểm tra trùng ngày nghỉ
`$row = $this->queryOne("SELECT id FROM leave_requests WHERE ... AND start_date <= ? AND end_date >= ?", $params);`
Giải thích:
Tìm xem có bản ghi đơn nghỉ nào ở trạng thái `'PENDING'` hoặc `'APPROVED'` mà ngày bắt đầu của đơn cũ nhỏ hơn hoặc bằng ngày kết thúc của đơn mới ứng tuyển (`start_date <= ?`), đồng thời ngày kết thúc đơn cũ lớn hơn hoặc bằng ngày bắt đầu của đơn mới ứng tuyển (`end_date >= ?`). Nếu tìm thấy bất kỳ bản ghi nào, chứng tỏ khoảng ngày bị trùng lặp.
Ví dụ:
Xin nghỉ từ `2026-05-22` đến `2026-05-25`. CSDL có một đơn cũ đã duyệt từ `2026-05-24` đến `2026-05-26` -> Trùng khớp và trả về `true`.

`public function create(...)`
Khai báo hàm tạo đơn nghỉ mới nhận mảng dữ liệu đầu vào.

2. Chèn đơn nghỉ phép trạng thái chờ duyệt
`return $this->execute("INSERT INTO leave_requests ... VALUES (?, ?, ?, ?, ?, 'PENDING', NOW(), NOW())", [ ... ]);`
Giải thích:
Thực thi câu SQL chèn dữ liệu đơn xin nghỉ mới vào bảng `leave_requests`, mặc định cột trạng thái `status` là `'PENDING'` (Chờ duyệt).

---

#### Bước 6: Trả kết quả về Client
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Sau khi thêm đơn nghỉ thành công, Controller thiết lập thông báo flash thành công gửi tới session và chuyển hướng người dùng về trang danh sách đơn xin nghỉ phép."
> * **File xử lý:** [LeaveController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/LeaveController.php) (Dòng 82-83)

```php
Session::flash('success', 'Gửi đơn nghỉ phép thành công. Chờ duyệt.');
$this->redirect('/leave');
```

=> giải thích: Đặt thông báo thành công và chuyển hướng trang
1. Tạo flash message
`Session::flash('success', 'Gửi đơn nghỉ phép thành công. Chờ duyệt.');`
Giải thích:
Tạo thông báo flash báo gửi đơn nghỉ thành công.

2. Điều hướng về danh sách đơn xin nghỉ phép
`$this->redirect('/leave');`
Giải thích:
Chuyển hướng trình duyệt về route `/leave` để xem trạng thái đơn nghỉ.""")

def add_approve_leave():
    add_part(r"""### 🤝 8. AJAX Duyệt đơn nghỉ phép (`/leave/approve`)

#### Bước 1: Client gửi dữ liệu
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Admin truy cập danh sách đơn nghỉ phép, chọn đơn đang chờ duyệt và click nút Duyệt. Trình duyệt gửi form POST chứa ID của đơn nghỉ lên máy chủ."
> * **File giao diện:** [index.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/views/leave/index.php)

```html
<form method="POST" action="<?= BASE_URL ?>/leave/approve" style="display:inline;">
    <input type="hidden" name="id" value="<?= $leave['id'] ?>">
    <button type="submit" class="btn btn-sm btn-success">Duyệt</button>
</form>
```

=> giải thích: Form gửi yêu cầu duyệt đơn nghỉ phép
1. Khai báo form gửi POST duyệt đơn nghỉ
`<form method="POST" action="<?= BASE_URL ?>/leave/approve" style="display:inline;">`
Giải thích:
Form gửi yêu cầu duyệt qua phương thức `POST` tới route `/leave/approve`.

2. Ô input ẩn chứa ID đơn nghỉ
`<input type="hidden" name="id" value="<?= $leave['id'] ?>">`
Giải thích:
Gửi ID của đơn xin nghỉ phép cần duyệt.
Ví dụ:
`value="8"` cho biết duyệt đơn nghỉ có ID là 8.

3. Nút bấm submit duyệt
`<button type="submit">Duyệt</button>`
Giải thích:
Nút bấm submit kích hoạt hành động gửi.

---

#### Bước 2: Controller bắt request POST
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Tại máy chủ, hàm approve() của LeaveController bắt request, lấy ID đơn nghỉ phép từ POST và ép kiểu số nguyên."
> * **File xử lý:** [LeaveController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/LeaveController.php) (Dòng 109)

```php
$id = (int)$this->post('id');
```

=> giải thích: Tiếp nhận ID đơn nghỉ cần duyệt
1. Lấy ID từ POST
`$id = (int)$this->post('id');`
Giải thích:
Nhận giá trị ID đơn nghỉ gửi lên từ POST và ép kiểu số nguyên để bảo mật.

---

#### Bước 3: Lấy thông tin đơn nghỉ & Kiểm tra hợp lệ
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Controller gọi Model tìm thông tin đơn nghỉ theo ID. Kiểm tra nếu đơn nghỉ không tồn tại hoặc trạng thái hiện tại khác PENDING thì báo lỗi 404 hủy bỏ yêu cầu."
> * **File xử lý:** [LeaveController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/LeaveController.php) (Dòng 110-113)

```php
$leave = $this->leaveModel->findById($id);
if (!$leave || $leave['status'] !== 'PENDING') $this->abort(404);

$employeeId = (int)$leave['employee_id'];
```

=> giải thích: Lấy thông tin đơn nghỉ và kiểm tra điều kiện
1. Lấy thông tin chi tiết đơn nghỉ
`$leave = $this->leaveModel->findById($id);`
Giải thích:
Truy vấn thông tin chi tiết của đơn nghỉ kèm theo thông tin nhân viên từ bảng liên kết.

2. Kiểm tra tính hợp lệ trạng thái đơn nghỉ
`if (!$leave || $leave['status'] !== 'PENDING') $this->abort(404);`
Giải thích:
Chỉ cho phép duyệt đơn đang ở trạng thái Chờ duyệt (`'PENDING'`). Nếu đơn đã duyệt, từ chối hoặc không tìm thấy đơn, lập tức trả lỗi 404.

3. Lấy ID nhân viên nghỉ phép
`$employeeId = (int)$leave['employee_id'];`
Giải thích:
Lấy ID nhân viên của đơn nghỉ để phục vụ cho việc xóa lịch xếp ca tương ứng.

---

#### Bước 4: Gọi Model cập trạng thái đơn & tự động gỡ ca làm việc trùng
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Controller gọi Model để cập nhật trạng thái đơn thành APPROVED và ID người duyệt. Đồng thời gọi Model WorkRecord để tự động tìm kiếm, gỡ bỏ tất cả các ca phân công làm việc của nhân sự đó trong khoảng ngày nghỉ."
> * **File xử lý:** [LeaveController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/LeaveController.php) (Dòng 116-123)

```php
$this->leaveModel->approve($id, Auth::id());

$removedAssignments = $this->workModel->removeAssignmentsByEmployeeInRange(
    $employeeId,
    $leave['start_date'],
    $leave['end_date'],
    'AUTO_REMOVED_DUE_TO_APPROVED_LEAVE'
);
```

=> giải thích: Duyệt đơn và gỡ lịch phân ca trùng ngày nghỉ
1. Duyệt đơn nghỉ phép
`$this->leaveModel->approve($id, Auth::id());`
Giải thích:
Gọi Model cập nhật trạng thái đơn thành `'APPROVED'` và truyền ID Admin duyệt (`Auth::id()`) vào CSDL.

2. Gỡ lịch làm việc trùng ngày nghỉ
`$removedAssignments = $this->workModel->removeAssignmentsByEmployeeInRange( ... );`
Giải thích:
Gọi phương thức từ `WorkRecordModel` gỡ các ca xếp lịch làm việc (`ASSIGNMENT`) của nhân viên này nằm trong khoảng từ ngày bắt đầu đến ngày kết thúc của đơn nghỉ vừa được duyệt.
Ví dụ:
Nguyễn Văn A xin nghỉ từ ngày `2026-05-22` đến `2026-05-25`. Khi duyệt đơn, hệ thống tự động gỡ bỏ ca làm việc của Nguyễn Văn A trong 4 ngày này và trả về danh sách các ca bị xóa.

---

#### Bước 5: Model thực thi SQL trong CSDL
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Model thực thi lệnh UPDATE đổi trạng thái đơn nghỉ thành APPROVED, cập nhật thông tin reviewed_by và reviewed_at. Đồng thời thực thi SQL xóa ca làm việc trong khoảng ngày nghỉ ở bảng work_records."
> * **File xử lý:** [LeaveRequestModel.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/models/LeaveRequestModel.php) (Dòng 89-96)

```php
public function approve(int $id, int $reviewedBy): void {
    $this->execute(
        "UPDATE leave_requests
         SET status = 'APPROVED', reviewed_by = ?, reviewed_at = NOW(), updated_at = NOW()
         WHERE id = ?",
        [$reviewedBy, $id]
    );
}
```

=> giải thích: SQL thực thi duyệt đơn nghỉ phép
`public function approve(int $id, int $reviewedBy): void`
Khai báo hàm duyệt đơn nhận ID đơn nghỉ và ID Admin duyệt.

1. SQL cập nhật trạng thái duyệt đơn nghỉ
`$this->execute("UPDATE leave_requests SET status = 'APPROVED', ... WHERE id = ?", [$reviewedBy, $id]);`
Giải thích:
Cập nhật cột `status = 'APPROVED'` (Đã duyệt), cột `reviewed_by` thành ID của Admin đang đăng nhập và ghi nhận thời gian duyệt qua `NOW()`.

---

#### Bước 6: Ghi nhật ký & Trả kết quả
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Sau khi cập nhật thành công, hệ thống ghi log audit chứa số ca xếp lịch tự động bị gỡ bỏ, tạo thông báo flash báo thành công và chuyển hướng Admin về danh sách."
> * **File xử lý:** [LeaveController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/LeaveController.php) (Dòng 125-137)

```php
$this->sysModel->writeAudit([
    'actor_user_id'       => Auth::id(),
    'employee_id'         => $employeeId,
    'related_entity_type' => 'leave_requests',
    'related_entity_id'   => $id,
    'action'              => 'APPROVE_LEAVE',
    'old_value'           => ['status' => 'PENDING'],
    'new_value'           => ['status' => 'APPROVED', 'removed_assignments' => count($removedAssignments)],
    'description'         => "Duyệt đơn nghỉ phép, gỡ " . count($removedAssignments) . " ca đã phân công.",
]);

Session::flash('success', 'Đã duyệt đơn nghỉ phép.');
$this->redirect('/leave');
```

=> giải thích: Ghi nhật ký duyệt đơn nghỉ và điều hướng
1. Ghi Audit Log duyệt đơn nghỉ
`$this->sysModel->writeAudit([ ... ]);`
Giải thích:
Ghi lại hành động duyệt đơn nghỉ phép, ghi rõ số lượng lịch phân công bị gỡ bỏ do trùng lịch nghỉ.

2. Tạo thông báo flash thành công
`Session::flash('success', 'Đã duyệt đơn nghỉ phép.');`
Giải thích:
Tạo session flash thông báo duyệt đơn thành công.

3. Điều hướng về danh sách đơn xin nghỉ phép
`$this->redirect('/leave');`
Giải thích:
Chuyển hướng trình duyệt của Admin về route `/leave`.""")

def add_update_attendance():
    add_part(r"""### ⏰ 9. AJAX Sửa chấm công (`/ajax/attendance/update`)

#### Bước 1: Client gửi dữ liệu (AJAX request)
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Admin click nút chỉnh sửa chấm công trên bảng công, JavaScript/jQuery lắng nghe sự kiện, hiển thị modal nhập liệu rồi gửi yêu cầu POST bằng AJAX để cập nhật dữ liệu không tải lại trang."
> * **File giao diện:** [index.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/views/attendance/index.php)

```javascript
$.ajax({
    url: BASE_URL + '/ajax/attendance/update',
    method: 'POST',
    data: {
        id: recordId,
        check_in_at: checkInVal,
        check_out_at: checkOutVal,
        note: reasonVal,
        late_minutes: lateVal,
        overtime_minutes: otVal
    },
    success: function(res) {
        if (res.success) {
            // Cập nhật thông tin giờ vào/ra và ghi chú mới trên dòng tương ứng của bảng
        }
    }
});
```

=> giải thích: JavaScript gửi yêu cầu chỉnh sửa chấm công bằng AJAX
1. Gửi Ajax POST cập nhật chấm công
`$.ajax({ url: BASE_URL + '/ajax/attendance/update', method: 'POST', data: { ... } })`
Giải thích:
Gửi request `POST` cập nhật bản ghi chấm công tới endpoint `/ajax/attendance/update`. Đối tượng `data` chứa thông tin ID bản ghi, thời gian vào/ra mới, lý do sửa (`note`), số phút đi muộn và số phút làm thêm.

---

#### Bước 2: Controller bắt request POST
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Tại máy chủ, hàm ajaxUpdate() của AttendanceController bắt đầu bằng việc cấu hình Header JSON, lấy ID bản ghi từ POST để thực hiện truy vấn."
> * **File xử lý:** [AttendanceController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/AttendanceController.php) (Dòng 100-102)

```php
public function ajaxUpdate(): void {
    header('Content-Type: application/json; charset=utf-8');
    $id  = (int)$this->post('id');
```

=> giải thích: Khởi tạo AJAX xử lý chấm công
1. Đặt kiểu nội dung JSON trả về
`header('Content-Type: application/json; charset=utf-8');`
Giải thích:
Đặt header đầu ra là JSON với bảng mã ký tự utf-8.

2. Lấy ID bản ghi chấm công
`$id = (int)$this->post('id');`
Giải thích:
Nhận giá trị ID bản ghi chấm công cần chỉnh sửa từ POST và ép kiểu số nguyên.

---

#### Bước 3: Validate & Lấy thông tin bản ghi chấm công
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Hệ thống lấy bản ghi chấm công cũ trong DB. Validate nếu trống giờ vào/ra hoặc trống lý do ghi chú sửa đổi thì lập tức xuất thông báo lỗi JSON và thoát sớm."
> * **File xử lý:** [AttendanceController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/AttendanceController.php) (Dòng 103-121)

```php
$row = $this->workModel->getAttendanceById($id);

if (!$row) {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy bản ghi chấm công.']);
    exit;
}

$checkIn  = $this->post('check_in_at');
$checkOut = $this->post('check_out_at');
$note     = trim($this->post('note', ''));

if (!$checkIn || !$checkOut) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ giờ vào/ra.']);
    exit;
}
if (empty($note)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập lý do chỉnh sửa.']);
    exit;
}
```

=> giải thích: Xác thực thông tin chỉnh sửa chấm công
1. Lấy thông tin chấm công hiện tại từ CSDL
`$row = $this->workModel->getAttendanceById($id);`
Giải thích:
Tìm bản ghi chấm công (`ATTENDANCE`) theo ID trong bảng `work_records`.

2. Kiểm tra sự tồn tại bản ghi
`if (!$row) { ... }`
Giải thích:
Nếu không tìm thấy bản ghi chấm công cần sửa, xuất JSON báo lỗi và dừng thực thi.

3. Lấy dữ liệu sửa đổi từ POST
`$checkIn = $this->post('check_in_at'); ...`
Giải thích:
Nhận thông tin giờ check-in, check-out và lý do sửa đổi từ POST.

4. Kiểm tra bắt buộc giờ vào/ra và lý do sửa
`if (!$checkIn || !$checkOut) { ... } if (empty($note)) { ... }`
Giải thích:
Bắt buộc Admin phải điền đầy đủ giờ vào/ra, đồng thời bắt buộc phải nhập lý do tại sao chỉnh sửa chấm công (để ghi log an toàn). Nếu thiếu, trả lỗi JSON và dừng chương trình.

---

#### Bước 4: Tính toán lại số phút làm việc & Gọi Model
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Hệ thống tính toán hiệu số giữa giờ ra và giờ vào thành số phút làm việc thực tế, trừ đi 30 phút nghỉ ngơi mặc định và gọi Model thực hiện cập nhật."
> * **File xử lý:** [AttendanceController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/AttendanceController.php) (Dòng 123-133)

```php
$workedMin = (int)((strtotime($checkOut) - strtotime($checkIn)) / 60 - ($row['break_minutes'] ?? 30));
$workedMin = max(0, $workedMin);

$this->workModel->updateAttendance($id, [
    'check_in_at'      => $checkIn,
    'check_out_at'     => $checkOut,
    'worked_minutes'   => $workedMin,
    'late_minutes'     => (int)$this->post('late_minutes', 0),
    'overtime_minutes' => (int)$this->post('overtime_minutes', 0),
    'note'             => $note,
]);
```

=> giải thích: Tính toán lại số phút làm việc và gọi cập nhật
1. Tính tổng số phút làm việc thực tế
`$workedMin = (int)((strtotime($checkOut) - strtotime($checkIn)) / 60 - ($row['break_minutes'] ?? 30));`
Giải thích:
Dùng `strtotime()` chuyển giờ vào/ra sang dạng timestamp giây. Lấy hiệu số chia cho 60 để quy đổi ra số phút, sau đó trừ đi số phút nghỉ ngơi giữa ca (`break_minutes`, mặc định là 30 phút). Ép kết quả thành kiểu số nguyên `(int)`.
Ví dụ:
Check-in `08:00`, check-out `17:00` (hiệu số 9 tiếng = 540 phút), trừ 30 phút nghỉ -> `$workedMin = 510`.

2. Đảm bảo số phút không âm
`$workedMin = max(0, $workedMin);`
Giải thích:
Hàm `max(0, $workedMin)` đảm bảo nếu có lỗi tính toán ra số âm thì số phút làm việc sẽ được gán bằng 0.

3. Gọi Model cập nhật bản ghi chấm công
`$this->workModel->updateAttendance($id, [ ... ]);`
Giải thích:
Gọi Model ghi nhận thời gian vào/ra, số phút làm việc, số phút đi muộn, làm thêm và lý do ghi chú mới vào CSDL.

---

#### Bước 5: Model thực thi SQL
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "WorkRecordModel thực thi câu lệnh SQL UPDATE cập nhật trực tiếp bản ghi chấm công trong bảng work_records lọc theo ID."
> * **File xử lý:** [WorkRecordModel.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/models/WorkRecordModel.php) (Dòng 368-385)

```php
public function updateAttendance(int $id, array $data): void {
    $this->execute(
        "UPDATE work_records
         SET check_in_at = ?, check_out_at = ?, worked_minutes = ?,
             late_minutes = ?, overtime_minutes = ?, note = ?,
             updated_at = NOW()
         WHERE id = ?",
        [
            $data['check_in_at'],
            $data['check_out_at'],
            $data['worked_minutes'],
            $data['late_minutes']     ?? 0,
            $data['overtime_minutes'] ?? 0,
            $data['note']             ?? null,
            $id,
        ]
    );
}
```

=> giải thích: SQL thực thi cập nhật bản ghi chấm công
`public function updateAttendance(int $id, array $data): void`
Khai báo hàm cập nhật chấm công nhận ID bản ghi và mảng dữ liệu.

1. SQL UPDATE bản ghi chấm công
`$this->execute("UPDATE work_records SET check_in_at = ?, check_out_at = ?, ... WHERE id = ?", [ ... ]);`
Giải thích:
Thực thi câu SQL cập nhật các thông tin giờ giấc chấm công, số phút công, lý do ghi chú và cập nhật thời gian sửa đổi `updated_at` trong bảng `work_records` theo ID.

---

#### Bước 6: Ghi nhật ký Audit & Trả về JSON
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Sau khi sửa thành công, hệ thống ghi log audit chứa thông tin cũ và mới, sau đó định dạng lại thời gian vào/ra để trả về kết quả JSON cho Client."
> * **File xử lý:** [AttendanceController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/AttendanceController.php) (Dòng 135-154)

```php
$this->sysModel->writeAudit([
    'actor_user_id'       => Auth::id(),
    'employee_id'         => (int)$row['employee_id'],
    'related_entity_type' => 'work_records',
    'related_entity_id'   => $id,
    'action'              => 'UPDATE_ATTENDANCE',
    'old_value'           => $row,
    'new_value'           => ['check_in_at' => $checkIn, 'check_out_at' => $checkOut, 'note' => $note],
    'description'         => "Admin chỉnh sửa chấm công ID {$id}. Lý do: {$note}",
]);

echo json_encode([
    'success'      => true,
    'message'      => 'Đã cập nhật chấm công.',
    'check_in'     => date('H:i', strtotime($checkIn)),
    'check_out'    => date('H:i', strtotime($checkOut)),
]);
exit;
```

=> giải thích: Ghi log Audit và trả phản hồi JSON thành công
1. Ghi Audit Log chỉnh sửa công làm việc
`$this->sysModel->writeAudit([ ... ]);`
Giải thích:
Lưu lại hoạt động thay đổi giờ công của Admin đối với nhân viên cụ thể kèm theo lý do sửa đổi.

2. Trả JSON thành công kèm thời gian định dạng lại
`echo json_encode([ 'success' => true, ..., 'check_in' => date('H:i', strtotime($checkIn)), ... ]); exit;`
Giải thích:
Mã hóa JSON trả thông tin thành công cùng thời gian vào/ra định dạng ngắn gọn `H:i` (Giờ:Phút) về cho giao diện cập nhật và thoát chương trình.
Ví dụ:
`{"success":true,"message":"Đã cập nhật chấm công.","check_in":"08:00","check_out":"17:00"}`.""")

def add_calculate_payroll():
    add_part(r"""### 💵 10. Tính lương tự động (`/payroll/calculate`)

#### Bước 1: Client gửi dữ liệu
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Admin truy cập trang quản lý kỳ lương, chọn kỳ lương cần tính và bấm nút Tính lương. Trình duyệt gửi request POST chứa ID kỳ lương lên máy chủ."
> * **File giao diện:** [index.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/views/payroll/index.php)

```html
<form method="POST" action="<?= BASE_URL ?>/payroll/calculate">
    <input type="hidden" name="period_id" value="<?= $period['id'] ?>">
    <button type="submit">Tính lương</button>
</form>
```

=> giải thích: Form gửi yêu cầu tính lương tự động cho kỳ lương
1. Khai báo form gửi POST tính lương
`<form method="POST" action="<?= BASE_URL ?>/payroll/calculate">`
Giải thích:
Định nghĩa form gửi yêu cầu tính toán bảng lương qua phương thức `POST` tới route `/payroll/calculate`.

2. Ô input ẩn truyền ID kỳ lương cần tính
`<input type="hidden" name="period_id" value="<?= $period['id'] ?>">`
Giải thích:
Input ẩn truyền ID kỳ lương cần tính.
Ví dụ:
`value="2"` biểu thị tính lương cho kỳ lương có ID bằng 2.

3. Nút bấm submit thực thi tính lương
`<button type="submit">Tính lương</button>`
Giải thích:
Nút submit gửi yêu cầu tính toán lên máy chủ.

---

#### Bước 2: Controller bắt request POST
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Hệ thống gọi hàm calculate() trong PayrollController, lấy ID kỳ lương từ POST, tìm kiếm thông tin kỳ lương trong DB, nếu không tìm thấy thì sập 404."
> * **File xử lý:** [PayrollController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/PayrollController.php) (Dòng 76-80)

```php
$periodId = (int)$this->post('period_id');
$period   = $this->payModel->getPeriodById($periodId);
if (!$period) $this->abort(404);
```

=> giải thích: Tiếp nhận ID kỳ lương cần tính toán
1. Lấy ID kỳ lương từ POST
`$periodId = (int)$this->post('period_id');`
Giải thích:
Nhận giá trị ID kỳ lương từ mảng POST và ép kiểu số nguyên.

2. Lấy thông tin chi tiết kỳ lương
`$period = $this->payModel->getPeriodById($periodId);`
Giải thích:
Gọi Model để truy vấn thông tin kỳ lương (gồm ngày bắt đầu, ngày kết thúc kỳ lương).

3. Hủy request nếu kỳ lương không tồn tại
`if (!$period) $this->abort(404);`
Giải thích:
Nếu không tìm thấy kỳ lương, trả trang lỗi 404.

---

#### Bước 3: Lấy danh sách nhân viên & Lặp tính toán
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Controller lấy toàn bộ danh sách nhân sự đang hoạt động và dùng vòng lặp foreach để truy vấn thông tin tổng hợp chấm công của từng người trong khoảng ngày của kỳ lương."
> * **File xử lý:** [PayrollController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/PayrollController.php) (Dòng 81-88)

```php
$employees = $this->userModel->findAll();

foreach ($employees as $emp) {
    $summary = $this->workModel->getAttendanceSummary(
        (int)$emp['id'],
        $period['period_start'],
        $period['period_end']
    );
```

=> giải thích: Lặp tính toán chấm công cho từng nhân viên
1. Lấy danh sách toàn bộ nhân viên
`$employees = $this->userModel->findAll();`
Giải thích:
Gọi Model lấy toàn bộ nhân sự chưa bị xóa mềm trong hệ thống để tính lương.

2. Vòng lặp duyệt qua từng nhân sự
`foreach ($employees as $emp) { ... }`
Giải thích:
Duyệt qua từng nhân viên trong danh sách.

3. Gọi Model lấy tổng hợp số liệu chấm công
`$summary = $this->workModel->getAttendanceSummary((int)$emp['id'], $period['period_start'], $period['period_end']);`
Giải thích:
Gọi phương thức từ `WorkRecordModel` tổng hợp số phút làm việc, số phút đi muộn, số phút làm thêm của nhân sự `$emp['id']` trong khoảng ngày bắt đầu (`period_start`) và kết thúc (`period_end`) của kỳ lương.

---

#### Bước 4: Áp dụng công thức tính lương & Upsert bản ghi lương
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Hệ thống lấy lương cơ bản, lương giờ, số phút làm việc của nhân sự rồi áp dụng công thức: Lương cứng = số giờ nhân lương giờ. OT nhân 1.5. Đi muộn bị phạt trừ 50% lương giờ. Cuối cùng thực hiện upsert ghi nhận bảng lương."
> * **File xử lý:** [PayrollController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/PayrollController.php) (Dòng 90-128)

```php
$baseSalary    = (float)$emp['base_salary'];
$hourlyRate    = (float)$emp['hourly_rate'];
$workedMin     = (int)$summary['total_worked'];
$overtimeMin   = (int)$summary['total_overtime'];
$lateMin       = (int)$summary['total_late'];

$baseAmount      = round(($workedMin / 60) * $hourlyRate, 2);
$overtimeAmount  = round(($overtimeMin / 60) * $hourlyRate * 1.5, 2);
$allowanceAmount = 0.0;
$deductionAmount = round(($lateMin / 60) * $hourlyRate * 0.5, 2);
$finalAmount     = $baseAmount + $overtimeAmount + $allowanceAmount - $deductionAmount;

$snapshot = [
    'employee_id'        => $emp['id'],
    'base_salary'        => $baseSalary,
    'hourly_rate'        => $hourlyRate,
    'worked_minutes'     => $workedMin,
    'overtime_minutes'   => $overtimeMin,
    'late_minutes'       => $lateMin,
    'calculated_at'      => date('Y-m-d H:i:s'),
];

$this->payModel->upsertItem([
    'period_id'        => $periodId,
    'employee_id'      => (int)$emp['id'],
    'period_start'     => $period['period_start'],
    'period_end'       => $period['period_end'],
    'base_amount'      => $baseAmount,
    'overtime_amount'  => $overtimeAmount,
    'allowance_amount' => $allowanceAmount,
    'deduction_amount' => $deductionAmount,
    'final_amount'     => $finalAmount,
    'snapshot'         => $snapshot,
    'created_by'       => Auth::id(),
]);
```

=> giải thích: Tính toán lương theo công thức và lưu vào CSDL
1. Nhận hệ số lương và ép kiểu số thực / số nguyên
`$baseSalary = (float)$emp['base_salary']; ...`
Giải thích:
Lấy lương cơ bản tháng, lương cơ bản giờ từ hồ sơ nhân sự, lấy số phút làm việc thực tế, đi muộn, làm thêm từ kết quả tổng hợp của Model, ép kiểu tương ứng.

2. Công thức tính lương cơ bản làm việc
`$baseAmount = round(($workedMin / 60) * $hourlyRate, 2);`
Giải thích:
Quy đổi số phút làm việc thành giờ (`$workedMin / 60`), nhân với lương giờ (`$hourlyRate`) để tính ra Lương cơ bản nhận được trong kỳ, làm tròn 2 chữ số thập phân bằng hàm `round()`.
Ví dụ:
Làm được 3000 phút (= 50 giờ), lương giờ là 100.000đ -> `$baseAmount = 5.000.000`.

3. Công thức tính lương làm thêm (OT)
`$overtimeAmount = round(($overtimeMin / 60) * $hourlyRate * 1.5, 2);`
Giải thích:
Lấy số giờ làm thêm nhân lương giờ và nhân hệ số OT làm thêm ngoài giờ là `1.5`.

4. Công thức tính tiền phạt đi muộn
`$deductionAmount = round(($lateMin / 60) * $hourlyRate * 0.5, 2);`
Giải thích:
Quy đổi số phút đi muộn thành giờ, nhân với lương giờ và nhân hệ số phạt là `0.5` (phạt trừ 50% lương giờ cho mỗi giờ đi muộn).

5. Công thức tính tổng lương thực lĩnh
`$finalAmount = $baseAmount + $overtimeAmount + $allowanceAmount - $deductionAmount;`
Giải thích:
Thực lĩnh bằng Lương cơ bản + Lương làm thêm + Phụ cấp (`allowanceAmount`) - Tiền phạt muộn (`deductionAmount`).

6. Khởi tạo bản ghi snapshot lưu lịch sử
`$snapshot = [ ... ];`
Giải thích:
Đóng gói các thông số tính toán thô tại thời điểm đó vào mảng `$snapshot` để lưu trữ dạng JSON trong CSDL, tránh việc sau này hồ sơ nhân sự thay đổi lương làm sai lệch lịch sử bảng lương cũ.

7. Gọi Model lưu chi tiết bảng lương nhân sự
`$this->payModel->upsertItem([ ... ]);`
Giải thích:
Gọi Model thực hiện thêm mới hoặc cập nhật thông tin lương của nhân sự này trong kỳ lương tương ứng.

---

#### Bước 5: Model thực thi SQL & Cập nhật trạng thái kỳ lương
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "PayrollModel kiểm tra xem bản ghi lương của nhân sự đó trong kỳ này đã tồn tại chưa ở upsertItem(). Nếu có chạy UPDATE, chưa có chạy INSERT. Sau đó cập nhật trạng thái kỳ lương thành CALCULATED."
> * **File xử lý:** [PayrollModel.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/models/PayrollModel.php) (Dòng 58-100) và [PayrollController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/PayrollController.php) (Dòng 130)

```php
// Trong PayrollModel.php
$existing = $this->queryOne(
    "SELECT id FROM payroll_records
     WHERE record_type = 'ITEM'
       AND parent_id   = ?
       AND employee_id = ?",
    [$data['period_id'], $data['employee_id']]
);
$snapshot = json_encode($data['snapshot'] ?? []);
if ($existing) {
    $this->execute("UPDATE payroll_records SET ... WHERE id = ?", [ ... ]);
} else {
    $this->execute("INSERT INTO payroll_records ...", [ ... ]);
}

// Trong PayrollController.php
$this->payModel->updatePeriodStatus($periodId, 'CALCULATED');
```

=> giải thích: Lưu trữ chi tiết bảng lương và cập nhật trạng thái kỳ lương
1. Tìm kiếm bản ghi lương cũ của nhân viên trong kỳ lương
`$existing = $this->queryOne("SELECT id FROM payroll_records WHERE record_type = 'ITEM' AND parent_id = ? AND employee_id = ?", [ ... ]);`
Giải thích:
Tìm xem nhân viên này đã được tính lương trong kỳ lương này chưa.

2. Mã hóa snapshot sang chuỗi JSON
`$snapshot = json_encode($data['snapshot'] ?? []);`
Giải thích:
Chuyển đổi mảng snapshot chứa số liệu thô thành chuỗi JSON để lưu trữ vào một cột duy nhất trong CSDL.
Ví dụ:
`{"employee_id":5,"base_salary":20000000,...}`.

3. SQL chèn mới hoặc cập nhật
`if ($existing) { ... } else { ... }`
Giải thích:
Nếu đã tồn tại (`$existing`), thực thi câu lệnh SQL UPDATE cập nhật lại bảng lương. Nếu chưa tồn tại, thực thi câu lệnh SQL INSERT chèn bản ghi lương mới với loại bản ghi là `'ITEM'` và trạng thái là `'READY'`.

4. Cập nhật trạng thái kỳ lương sang CALCULATED
`$this->payModel->updatePeriodStatus($periodId, 'CALCULATED');`
Giải thích:
Gọi Model chạy câu UPDATE đổi trạng thái của kỳ lương (`PERIOD`) từ `'DRAFT'` sang `'CALCULATED'` (Đã tính toán).

---

#### Bước 6: Ghi nhật ký & Điều hướng
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Sau khi tính lương hoàn tất, hệ thống ghi log audit hoạt động tính toán bảng lương, tạo thông báo flash báo thành công và chuyển hướng Admin về trang quản lý lương."
> * **File xử lý:** [PayrollController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/PayrollController.php) (Dòng 132-141)

```php
$this->sysModel->writeAudit([
    'actor_user_id'       => Auth::id(),
    'related_entity_type' => 'payroll_records',
    'related_entity_id'   => $periodId,
    'action'              => 'CALCULATE_PAYROLL',
    'description'         => "Tính lương kỳ ID {$periodId} cho " . count($employees) . " nhân viên.",
]);

Session::flash('success', 'Đã tính xong lương. Vui lòng kiểm tra trước khi publish.');
$this->redirect('/payroll');
```

=> giải thích: Ghi nhật ký tính lương và điều hướng trang
1. Ghi Audit Log hoạt động tính lương
`$this->sysModel->writeAudit([ ... ]);`
Giải thích:
Ghi log hoạt động tính lương cho kỳ lương tương ứng kèm theo số lượng nhân viên được tính.

2. Tạo thông báo flash thành công
`Session::flash('success', 'Đã tính xong lương. Vui lòng kiểm tra trước khi publish.');`
Giải thích:
Tạo session flash thông báo tính lương thành công.

3. Điều hướng về trang quản lý lương
`$this->redirect('/payroll');`
Giải thích:
Chuyển hướng trình duyệt về route `/payroll` để xem bảng lương chi tiết.""")

def add_footer():
    add_part(r"""---

## 🗺️ Phần 4: Bản đồ định tuyến URL (URL Routing Map)

> [!NOTE]
> Khi giáo viên hỏi: *"Một URL cụ thể trên trình duyệt sẽ được xử lý bởi file nào, hàm nào và phân quyền ra sao?"*, bạn đối chiếu bảng dưới đây để trả lời:

| URL Route (Đường dẫn) | HTTP Method | Class Controller (Lớp xử lý) | Action Method (Hàm xử lý) | Phân quyền truy cập | Mô tả nghiệp vụ |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **`auth/login`** | `GET` / `POST` | `AuthController` | `login()` | Công khai (`[]`) | Hiển thị form và xử lý Đăng nhập |
| **`auth/logout`** | `GET` | `AuthController` | `logout()` | Đã đăng nhập (`null`) | Đăng xuất, hủy Session người dùng |
| **`home`** hoặc rỗng | `GET` | `HomeController` | `index()` | Đã đăng nhập (`null`) | Hiển thị trang chủ / Dashboard thống kê |
| **`profile`** | `GET` | `ProfileController` | `index()` | Đã đăng nhập (`null`) | Xem thông tin cá nhân của nhân sự |
| **`employees`** | `GET` | `EmployeeController` | `index()` | Quản trị viên (`['ADMIN']`) | Hiển thị danh sách nhân sự (Admin) |
| **`employees/create`** | `GET` | `EmployeeController` | `create()` | Quản trị viên (`['ADMIN']`) | Hiển thị form Thêm nhân sự mới |
| **`employees/store`** | `POST` | `EmployeeController` | `store()` | Quản trị viên (`['ADMIN']`) | Xử lý chèn dữ liệu nhân sự mới |
| **`employees/edit`** | `GET` | `EmployeeController` | `edit()` | Quản trị viên (`['ADMIN']`) | Hiển thị form Sửa thông tin nhân sự |
| **`employees/update`** | `POST` | `EmployeeController` | `update()` | Quản trị viên (`['ADMIN']`) | Xử lý cập nhật thông tin nhân sự |
| **`employees/delete`** | `POST` | `EmployeeController` | `delete()` | Quản trị viên (`['ADMIN']`) | Xử lý xóa mềm tài khoản nhân sự |
| **`leave`** | `GET` | `LeaveController` | `index()` | Đã đăng nhập (`null`) | Danh sách đơn xin nghỉ phép của mình/mọi người |
| **`leave/create`** | `GET` | `LeaveController` | `create()` | Đã đăng nhập (`null`) | Giao diện viết đơn xin nghỉ phép |
| **`leave/store`** | `POST` | `LeaveController` | `store()` | Đã đăng nhập (`null`) | Gửi đơn xin nghỉ phép, kiểm tra overlap |
| **`leave/approve`** | `POST` | `LeaveController` | `approve()` | Quản trị viên (`['ADMIN']`) | Phê duyệt đơn nghỉ, tự động gỡ ca làm việc |
| **`attendance`** | `GET` | `AttendanceController` | `index()` | Đã đăng nhập (`null`) | Xem bảng chấm công cá nhân hoặc của toàn bộ nhân viên |
| **`attendance/update`** | `POST` | `AttendanceController` | `update()` | Quản trị viên (`['ADMIN']`) | Sửa chấm công trực tiếp bằng form thường |
| **`payroll`** | `GET` | `PayrollController` | `index()` | Đã đăng nhập (`null`) | Xem phiếu lương cá nhân hoặc danh sách kỳ lương |
| **`payroll/calculate`** | `POST` | `PayrollController` | `calculate()` | Quản trị viên (`['ADMIN']`) | Tính lương tự động cho toàn bộ nhân sự theo kỳ |
| **`ajax/employees/search`** | `GET` | `EmployeeController` | `ajaxSearch()` | Quản trị viên (`['ADMIN']`) | Tìm kiếm nhân viên realtime (AJAX JSON) |
| **`ajax/employees/toggle-status`** | `POST` | `EmployeeController` | `ajaxToggleStatus()` | Quản trị viên (`['ADMIN']`) | Khóa/Mở khóa tài khoản (AJAX JSON) |
| **`ajax/attendance/update`** | `POST` | `AttendanceController` | `ajaxUpdate()` | Quản trị viên (`['ADMIN']`) | Cập nhật giờ chấm công (AJAX JSON) |

Chúc các bạn ôn tập tốt và đạt kết quả cao trong kỳ thi vấn đáp Lập trình Web! 🎓""")

if __name__ == "__main__":
    add_header()
    add_login()
    add_create_employee()
    add_update_employee()
    add_delete_employee()
    add_search_employee()
    add_toggle_status()
    add_create_leave()
    add_approve_leave()
    add_update_attendance()
    add_calculate_payroll()
    add_footer()
    write_file()
