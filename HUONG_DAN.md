# 📘 Tài liệu Ôn thi Chi tiết từng dòng code — HRCore HR Management System

Tài liệu này được thiết kế đặc biệt để giúp bạn học thuộc lòng, hiểu sâu bản chất code và trả lời thi vấn đáp xuất sắc. Mỗi chức năng được bóc tách chi tiết qua 6 bước (từ Client đến Controller, Model, Database và ngược lại) kèm theo code thực tế và giải thích chi tiết cho từng dòng/khối lệnh.

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
>     *   *Bắt ngoại lệ (Exception):* Sử dụng khối lệnh `try { ... } catch (Exception $e) { ... }` để bắt các lỗi phát sinh ngoài dự kiến (như lỗi kết nối DB, lỗi trùng lặp khóa ngoại) để hiển thị thông báo lỗi thân thiện thay vì làm sập trang web.

---

## 🔍 Phần 3: Chi tiết Sự kiện & Logic của 10 Chức năng cốt lõi

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

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `<form method="POST" action="<?= BASE_URL ?>/auth/login" id="loginForm">`: Thẻ `form` khai báo gửi bằng phương thức `POST` (bảo mật khi truyền mật khẩu) và thuộc tính `action` dẫn tới route xử lý đăng nhập `/auth/login`.
> - `<input type="email" name="email" required>`: Ô nhập Email. Thuộc tính `name="email"` dùng để server nhận diện làm khóa POST và `required` bắt buộc nhập định dạng email hợp lệ phía client.
> - `<input type="password" name="password" id="password" required>`: Ô nhập mật khẩu ẩn ký tự, có `name="password"` làm khóa và thuộc tính `required` bắt buộc nhập.
> - `<button type="submit" id="loginBtn">Đăng nhập</button>`: Nút bấm kích hoạt hành động gửi form (sự kiện `submit`).
> - `document.getElementById('loginForm').addEventListener('submit', function() { ... })`: Đoạn mã JavaScript tìm kiếm form có id `loginForm` và gắn trình lắng nghe sự kiện `submit` để thực thi khi người dùng bấm gửi form.
> - `document.getElementById('loginBtn').textContent = 'Đang xử lý...';`: Đổi chữ nút đăng nhập thành "Đang xử lý..." để ngăn chặn hành vi click đúp liên tục của người dùng.

#### Bước 2: Controller bắt request POST
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Tại máy chủ, AuthController bắt request xem phương thức gửi lên có phải là POST hay không bằng cách gọi hàm tiện ích isPost()."
> * **File xử lý:** [AuthController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/AuthController.php) (Dòng 24)

```php
if ($this->isPost()) {
```

> [!TIP]
> **Giải thích chi tiết dòng code:**
> - `if ($this->isPost()) {`: Kiểm tra xem request gửi lên có phương thức là `POST` hay không. Hàm `isPost()` kiểm tra biến siêu toàn cục `$_SERVER['REQUEST_METHOD'] === 'POST'`. Nếu đúng là `POST`, tiến hành xử lý đăng nhập; nếu là `GET`, bỏ qua khối lệnh này để chỉ hiển thị giao diện form.

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

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `$email = trim($this->post('email', ''));`: Gọi phương thức `$this->post('email', '')` từ lớp cha `Controller.php` để lấy giá trị từ mảng `$_POST['email']`. Sau đó, hàm `trim()` loại bỏ toàn bộ khoảng trắng thừa ở hai đầu email.
> - `$password = $this->post('password', '');`: Lấy giá trị của mật khẩu từ `$_POST['password']`, mặc định là chuỗi rỗng nếu trống.
> - `if (empty($email) || empty($password)) {`: Kiểm tra nếu một trong hai biến `$email` hoặc `$password` bị trống bằng hàm `empty()`.
> - `$errors[] = 'Vui lòng nhập email và mật khẩu.';`: Đẩy chuỗi thông báo lỗi vào mảng `$errors` để trả về giao diện.

#### Bước 4: Gọi Model truy vấn thông tin
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Khi dữ liệu đầu vào đầy đủ, Controller gọi UserModel truy tìm thông tin nhân viên trong CSDL tương ứng với email đã nhập."
> * **File xử lý:** [AuthController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/AuthController.php) (Dòng 31)

```php
$user = $this->userModel->findByEmail($email);
```

> [!TIP]
> **Giải thích chi tiết dòng code:**
> - `$user = $this->userModel->findByEmail($email);`: Controller gọi phương thức `findByEmail()` từ đối tượng `$this->userModel` (một thể hiện của lớp `UserModel`) và truyền vào biến `$email` làm tham số. Phương thức sẽ trả về mảng thông tin tài khoản hoặc `null` nếu không tìm thấy.

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

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `public function findByEmail(string $email): ?array {`: Định nghĩa phương thức nhận vào tham số `$email` kiểu string và trả về một mảng (`array`) hoặc `null` (`?array`).
> - `return $this->queryOne( ... );`: Gọi phương thức `queryOne()` của lớp cha `Model.php` để thực thi câu lệnh SQL và trả về một bản ghi duy nhất.
> - `"SELECT * FROM users WHERE email = ? AND deleted_at IS NULL"`: Lệnh SQL tìm kiếm mọi trường thông tin từ bảng `users` thỏa mãn điều kiện `email` khớp với tham số truyền vào (`email = ?`) và tài khoản chưa bị xóa mềm (`deleted_at IS NULL`). Dấu hỏi chấm `?` giúp chống lại lỗi bảo mật SQL Injection.
> - `[$email]`: Mảng chứa tham số thực tế để PDO binding thay thế vào dấu hỏi chấm `?`.

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

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `if (!$user || !$this->userModel->verifyPassword($password, $user['password_hash'])) {`: So khớp mật khẩu thuần và chuỗi băm bảo mật bcrypt thông qua hàm `password_verify()` được bọc trong `verifyPassword()`.
> - `$errors[] = 'Email hoặc mật khẩu không chính xác.';`: Báo lỗi đăng nhập sai thông tin.
> - `elseif ($user['status'] !== 'ACTIVE') {`: Kiểm tra nếu trạng thái tài khoản khác `'ACTIVE'` (đã bị khóa).
> - `$errors[] = 'Tài khoản của bạn đã bị khóa hoặc vô hiệu hóa.';`: Đẩy thông báo lỗi khóa tài khoản.
> - `Auth::login($user);`: Gọi phương thức tĩnh của lớp `Auth.php` để lưu trữ thông tin nhận dạng nhân sự vào Session (`$_SESSION['user_id']`, `$_SESSION['user_role']`).
> - `$this->userModel->recordLastLogin((int)$user['id']);`: Gọi Model cập nhật thời gian đăng nhập gần nhất (`last_login_at = NOW()`).
> - `$this->redirect('/home');`: Gọi phương thức để chuyển hướng trình duyệt của người dùng sang trang chủ `/home`.

---

### 👥 2. Thêm nhân viên mới (`/employees/store`)

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

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `<form method="POST" action="<?= BASE_URL ?>/employees/store" id="createForm">`: Định nghĩa form gửi POST đến route thêm nhân sự.
> - `<input type="text" name="full_name" required>`: Trường nhập Họ tên nhân viên, gửi đi dưới khóa POST `full_name`.
> - `<input type="email" name="email" required>`: Trường nhập Email, bắt buộc nhập.
> - `<input type="password" name="password" minlength="8" required>`: Trường mật khẩu, bắt buộc tối thiểu 8 ký tự phía client.
> - `<button type="submit">Lưu lại</button>`: Nút submit gửi dữ liệu.

#### Bước 2: Controller bắt request POST
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Máy chủ tiếp nhận request và chuyển hướng tới hàm store() của EmployeeController, tại đây controller gán toàn bộ mảng POST vào biến data."
> * **File xử lý:** [EmployeeController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/EmployeeController.php) (Dòng 53-54)

```php
public function store(): void {
    $data   = $_POST;
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `public function store(): void {`: Khai báo phương thức `store` xử lý chèn dữ liệu nhân sự.
> - `$data = $_POST;`: Lưu mảng dữ liệu siêu toàn cục `$_POST` gửi lên vào biến cục bộ `$data` để xử lý.

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

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `$errors = $this->validateEmployee($data);`: Gọi hàm phụ `validateEmployee()` kiểm tra họ tên không trống, email hợp lệ và mật khẩu tối thiểu 8 ký tự.
> - `if (empty($errors) && $this->userModel->findByEmail($data['email'])) {`: Kiểm tra trùng lặp email. Nếu email đã thuộc về một tài khoản khác trong CSDL:
> - `$errors[] = 'Email này đã được sử dụng.';`: Đẩy thông báo lỗi trùng email vào danh sách lỗi.
> - `if (!empty($errors)) {`: Nếu có lỗi, gọi phương thức `$this->render()` để tải lại form thêm kèm mảng lỗi và dữ liệu đã nhập cũ (`old`), thoát hàm bằng lệnh `return;`.

#### Bước 4: Gọi Model & Tự động sinh mã nhân sự
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Nếu không có lỗi nào, Controller gọi hàm sinh mã nhân viên mới tự động (ví dụ EMP0002) và chuyển tiếp cho Model thực hiện chèn dữ liệu."
> * **File xử lý:** [EmployeeController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/EmployeeController.php) (Dòng 67-68)

```php
$data['employee_code'] = $this->userModel->generateEmployeeCode();
$newId = $this->userModel->create($data);
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `$data['employee_code'] = $this->userModel->generateEmployeeCode();`: Gọi hàm sinh mã tự động từ Model, ví dụ lấy mã lớn nhất hiện tại `EMP0007` sinh ra mã nhân sự tiếp theo là `EMP0008` và ghi đè vào mảng dữ liệu.
> - `$newId = $this->userModel->create($data);`: Chuyển mảng dữ liệu hoàn chỉnh cho phương thức `create` của Model và nhận lại ID tự sinh (Primary Key) của bản ghi vừa chèn.

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

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `public function create(array $data): int {`: Phương thức nhận mảng dữ liệu `$data` và trả về số nguyên ID nhân sự.
> - `$sql = "INSERT INTO users ... VALUES (?, ?, ...)";`: Câu lệnh SQL INSERT chỉ định danh sách cột và các placeholder `?` tương ứng cho kỹ thuật SQL Parameter Binding.
> - `return $this->execute($sql, [ ... ]);`: Gọi phương thức `execute` từ lớp cha `Model.php` thực thi SQL.
> - `password_hash($data['password'], PASSWORD_BCRYPT)`: Mã hóa mật khẩu bảo mật một chiều bằng thuật toán BCrypt trước khi lưu trữ dưới CSDL.
> - `?? null / ?? 0`: Toán tử kiểm tra Null Coalescing gán giá trị mặc định cho các cột tùy chọn không bắt buộc nhập.

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

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `$this->sysModel->writeAudit([ ... ]);`: Ghi nhận hoạt động vào bảng `system_records` (lưu ID Admin thực hiện, đối tượng tác động, mã hành động `CREATE_EMPLOYEE` và mô tả).
> - `Session::flash('success', 'Tạo nhân viên thành công.');`: Lưu thông báo thành công ngắn hạn hiển thị ở giao diện tiếp theo.
> - `$this->redirect('/employees');`: Chuyển hướng người dùng về trang danh sách nhân viên `/employees`.

---

### 📝 3. Chỉnh sửa nhân viên (`/employees/update`)

#### Bước 1: Client gửi dữ liệu
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Admin thay đổi thông tin nhân viên tại giao diện Chỉnh sửa, Form chứa thẻ hidden ID nhân viên được submit gửi POST lên Server."
> * **File giao diện:** [edit.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/views/employees/edit.php)

```html
<form method="POST" action="<?= BASE_URL ?>/employees/update">
    <input type="hidden" name="id" value="<?= $employee['id'] ?>">
    <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($employee['full_name']) ?>" required>
    <button type="submit">Cập nhật</button>
</form>
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `<form method="POST" action="<?= BASE_URL ?>/employees/update">`: Khai báo form gửi POST đến đường dẫn cập nhật.
> - `<input type="hidden" name="id" value="<?= $employee['id'] ?>">`: Thẻ input ẩn cực kỳ quan trọng chứa ID nhân viên. Dữ liệu này dùng cho mệnh đề `WHERE id = ?` ở câu truy vấn SQL UPDATE.
> - `value="<?= htmlspecialchars($employee['full_name']) ?>"`: Đổ dữ liệu Họ tên hiện tại của nhân sự từ database vào ô nhập, bọc trong hàm `htmlspecialchars()` để phòng chống lỗi bảo mật XSS.
> - `<button type="submit">`: Nút bấm submit gửi form cập nhật.

#### Bước 2: Controller bắt request POST
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Tại server, Controller tiếp nhận request POST cập nhật và lấy giá trị ID của nhân sự cần cập nhật."
> * **File xử lý:** [EmployeeController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/EmployeeController.php) (Dòng 91-92)

```php
public function update(): void {
    $id       = (int)$this->post('id');
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `public function update(): void {`: Khai báo phương thức cập nhật thông tin nhân viên.
> - `$id = (int)$this->post('id');`: Đọc giá trị ID gửi lên từ input ẩn và ép kiểu sang số nguyên `(int)` để đảm bảo an toàn truy vấn.

#### Bước 3: Validate & lấy dữ liệu
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Controller truy xuất thông tin cũ để so sánh ghi log, đồng thời thực hiện validate kiểm tra Họ tên không được bỏ trống."
> * **File xử lý:** [EmployeeController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/EmployeeController.php) (Dòng 93-105)

```php
$employee = $this->userModel->findById($id);
if (!$employee) $this->abort(404);

$old    = $employee;
$data   = $_POST;
$errors = [];

if (empty($data['full_name'])) {
    $errors[] = 'Họ tên không được để trống.';
}

if (!empty($errors)) {
    $this->render('employees/edit', ['employee' => $employee, 'errors' => $errors]);
    return;
}
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `$employee = $this->userModel->findById($id);`: Lấy thông tin bản ghi hiện tại của nhân viên trong CSDL.
> - `if (!$employee) $this->abort(404);`: Nếu không có bản ghi (null), ngắt chương trình và trả về mã lỗi 404.
> - `$old = $employee;`: Lưu trạng thái cũ vào biến `$old` để làm lịch sử so sánh ghi log.
> - `if (empty($data['full_name'])) {`: Kiểm tra nếu trường Họ tên mới nhập bị rỗng. Nếu rỗng, đẩy lỗi và tải lại giao diện sửa qua `$this->render()`, dùng `return` kết thúc sớm.

#### Bước 4: Gọi Model thực hiện sửa đổi
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Nếu mọi kiểm tra đều hợp lệ, Controller chuyển tiếp ID và dữ liệu POST mới cho Model để cập nhật."
> * **File xử lý:** [EmployeeController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/EmployeeController.php) (Dòng 107)

```php
$this->userModel->update($id, $data);
```

> [!TIP]
> **Giải thích chi tiết dòng code:**
> - `$this->userModel->update($id, $data);`: Gọi phương thức `update` của đối tượng `userModel`, truyền vào ID nhân sự cần sửa đổi và mảng dữ liệu mới `$data`.

#### Bước 5: Model thực thi SQL
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Model duyệt qua các trường được phép chỉnh sửa, xây dựng câu lệnh SQL UPDATE động để cập nhật dữ liệu vào DB."
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

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `$fields = []; $values = [];`: Khởi tạo mảng lưu danh sách câu lệnh gán SQL (`$fields`) và mảng lưu các giá trị thực tế tương ứng (`$values`).
> - `$allowed = [ ... ]`: Mảng trắng liệt kê danh sách các cột an toàn được phép cập nhật để chống lỗ hổng Mass Assignment.
> - `foreach ($allowed as $field) { ... }`: Vòng lặp duyệt qua từng trường được phép cập nhật.
> - `if (array_key_exists($field, $data)) { ... }`: Kiểm tra nếu trong mảng `$data` client gửi lên có chứa cột đó.
>   - `$fields[] = "{$field} = ?";`: Thêm chuỗi `"tên_cột = ?"` vào danh sách.
>   - `$values[] = $data[$field];`: Thêm giá trị tương ứng vào mảng.
> - `if (empty($fields)) return false;`: Nếu không có trường nào thay đổi, thoát hàm sớm.
> - `$fields[] = "updated_at = NOW()";`: Thêm câu lệnh tự động cập nhật thời gian chỉnh sửa gần nhất.
> - `$values[] = $id;`: Đẩy ID nhân sự vào cuối mảng để binding vào dấu hỏi chấm `?` ở mệnh đề `WHERE id = ?`.
> - `$this->execute( "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?", $values );`: Nối chuỗi mảng `$fields` phân tách bằng dấu phẩy `, ` để tạo nên câu lệnh SQL UPDATE động hoàn chỉnh và thực thi an toàn.

#### Bước 6: Trả kết quả về Client
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Controller ghi lịch sử thay đổi gồm dữ liệu cũ và mới vào Audit log, lưu thông báo thành công và chuyển hướng về danh sách."
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

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `$this->sysModel->writeAudit([ ... ])`: Ghi log kiểm toán lưu vết cả giá trị cũ (`old_value`) và các trường dữ liệu POST mới được cập nhật (`new_value`) để phục vụ quản lý lịch sử.
> - `Session::flash('success', 'Cập nhật thành công.');`: Lưu thông báo cập nhật thành công ngắn hạn.
> - `$this->redirect('/employees');`: Chuyển hướng người dùng quay lại trang danh sách nhân viên.

---

### 🗑️ 4. Xóa mềm nhân viên (`/employees/delete`)

#### Bước 1: Client gửi dữ liệu
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Admin click Xóa, trình duyệt hiển thị pop-up cảnh báo xác nhận. Nếu đồng ý, form chứa ID ẩn của nhân viên sẽ submit lên Server."
> * **File giao diện:** [index.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/views/employees/index.php)

```html
<form method="POST" action="<?= BASE_URL ?>/employees/delete" onsubmit="return confirm('Bạn có chắc chắn muốn xóa nhân viên này khỏi hệ thống?')">
    <input type="hidden" name="id" value="<?= $emp['id'] ?>">
    <button type="submit">Xóa</button>
</form>
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `onsubmit="return confirm('...')"`: Sự kiện JS inline bắt hành động gửi form. Hàm `confirm()` hiển thị hộp thoại xác nhận. Nếu người dùng chọn Cancel, hàm trả về `false`, ngăn chặn gửi form POST.
> - `<input type="hidden" name="id" value="<?= $emp['id'] ?>">`: Thẻ input ẩn lưu ID nhân sự cần xóa.

#### Bước 2: Controller bắt request POST
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Tại server, Controller tiếp nhận request POST tại đường dẫn xóa và lấy ID nhân viên từ POST."
> * **File xử lý:** [EmployeeController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/EmployeeController.php) (Dòng 148)

```php
public function delete(): void {
    $id = (int)$this->post('id');
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `public function delete(): void {`: Khai báo phương thức xóa.
> - `$id = (int)$this->post('id');`: Đọc ID nhân sự cần xóa từ POST và ép kiểu số nguyên để bảo mật.

#### Bước 3: Validate kiểm tra logic xóa
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Controller kiểm tra xem ID tồn tại hay không và ngăn chặn triệt để hành động Admin tự xóa tài khoản của chính mình."
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

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `if ($id === Auth::id()) {`: Kiểm tra nếu ID nhân viên gửi lên trùng khớp với ID của Admin hiện tại đang đăng nhập.
> - `Session::flash('error', 'Bạn không thể tự xóa tài khoản của chính mình.');`: Phát cảnh báo lỗi và chặn hành động bằng cách gán thông báo lỗi vào Session.
> - `$this->redirect('/employees'); return;`: Chuyển hướng ngay lập tức về danh sách và thoát hàm sớm để đảm bảo tài khoản Admin đang chạy không bao giờ bị xóa.
> - `$employee = $this->userModel->findById($id);`: Lấy thông tin tài khoản nhân sự từ Database để kiểm tra sự tồn tại.
> - `if (!$employee) $this->abort(404);`: Nếu ID không khớp với bất kỳ nhân sự nào, dừng chương trình và trả lỗi 404.

#### Bước 4: Gọi Model thực hiện Xóa mềm (Soft Delete)
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Controller gọi hàm softDelete của Model để đánh dấu xóa thay vì xóa vĩnh viễn trong CSDL."
> * **File xử lý:** [EmployeeController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/EmployeeController.php) (Dòng 159)

```php
$this->userModel->softDelete($id);
```

> [!TIP]
> **Giải thích chi tiết dòng code:**
> - `$this->userModel->softDelete($id);`: Gọi phương thức `softDelete` của UserModel để xử lý ẩn bản ghi nhân sự.

#### Bước 5: Model thực thi SQL
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Model không dùng DELETE mà dùng lệnh SQL UPDATE đặt thời gian deleted_at thành thời gian hiện tại để bảo toàn toàn vẹn dữ liệu chấm công."
> * **File xử lý:** [UserModel.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/models/UserModel.php) (Dòng 243-248)

```php
public function softDelete(int $id): void {
    $this->execute(
        "UPDATE users SET deleted_at = NOW(), updated_at = NOW() WHERE id = ?",
        [$id]
    );
}
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `public function softDelete(int $id): void {`: Định nghĩa phương thức xóa mềm.
> - `"UPDATE users SET deleted_at = NOW(), updated_at = NOW() WHERE id = ?"`: Cập nhật cột `deleted_at` thành thời gian hiện tại thay vì xóa vật lý bản ghi. Điều này bảo toàn toàn vẹn dữ liệu chấm công, lịch trực, phiếu lương cũ của nhân sự trong DB.
> - `[$id]`: Truyền ID để binding an toàn vào placeholder `?`.

#### Bước 6: Trả kết quả về Client
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Controller ghi lịch sử hành động xóa, thông báo thành công và chuyển hướng trình duyệt quay lại trang danh sách nhân sự."
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

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `$this->sysModel->writeAudit([ ... ])`: Ghi log kiểm toán lưu vết Admin đã thực hiện xóa nhân sự.
> - `Session::flash('success', ...)`: Thiết lập flash message thông báo xóa thành công.
> - `$this->redirect('/employees');`: Chuyển hướng trình duyệt quay về trang quản lý nhân viên.

---

### 🔍 5. Tìm kiếm nhân viên realtime bằng AJAX (`/ajax/employees/search`)

#### Bước 1: Client gửi dữ liệu (AJAX)
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Khi Admin gõ phím tìm kiếm, JS lắng nghe sự kiện input. Dùng kỹ thuật Debounce trì hoãn 300ms rồi gửi yêu cầu AJAX GET truyền các tham số lọc lên Server."
> * **File giao diện:** [index.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/views/employees/index.php)

```javascript
let searchTimer;
$('#liveSearch').on('input', function() {
    clearTimeout(searchTimer);
    const q      = $(this).val().trim();
    const dept   = $('select[name="department"]').val();
    const role   = $('select[name="role"]').val();
    const status = $('select[name="status"]').val();

    searchTimer = setTimeout(function() {
        $.ajax({
            url: BASE_URL + '/ajax/employees/search',
            method: 'GET',
            data: { q, department: dept, role, status },
            success: function(res) {
                if (res.success) {
                    renderEmployeeRows(res.data);
                    lucide.createIcons();
                }
            }
        });
    }, 300);
});
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `let searchTimer;`: Biến toàn cục lưu trữ ID hẹn giờ phục vụ kỹ thuật Debounce.
> - `$('#liveSearch').on('input', function() { ... })`: JQuery lắng nghe sự kiện gõ phím (`input`) trên ô nhập liệu tìm kiếm.
> - `clearTimeout(searchTimer);`: Xóa bộ hẹn giờ cũ mỗi khi người dùng gõ thêm ký tự mới, giúp ngăn chặn gửi request liên tục làm nghẽn server.
> - `const q = $(this).val().trim();`: Lấy từ khóa tìm kiếm và cắt khoảng trắng thừa hai đầu.
> - `const dept = ...; const role = ...; const status = ...;`: Lấy giá trị của các bộ lọc phòng ban, vai trò, trạng thái.
> - `searchTimer = setTimeout(function() { ... }, 300);`: Thiết lập bộ đếm thời gian mới 300ms. Chỉ khi người dùng ngừng gõ 300ms thì mới gửi request.
> - `$.ajax({ ... })`: Khởi chạy request AJAX bất đồng bộ.
> - `url: BASE_URL + '/ajax/employees/search'`: Đường dẫn endpoint xử lý tìm kiếm phía máy chủ.
> - `method: 'GET'`: Sử dụng phương thức GET để truy vấn đọc dữ liệu.
> - `data: { q, department: dept, role, status }`: Đóng gói và gửi các tham số lọc lên Server.
> - `success: function(res) { ... }`: Hàm callback chạy khi Server phản hồi dữ liệu thành công.
> - `renderEmployeeRows(res.data);`: Hàm Javascript vẽ lại cấu trúc các hàng trong bảng dựa trên kết quả mảng dữ liệu JSON nhận về.
> - `lucide.createIcons();`: Vẽ lại các icon biểu thị hiển thị trên giao diện do các dòng HTML cũ đã bị ghi đè.

#### Bước 2: Controller bắt request GET
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Tại server, Controller tiếp nhận request GET tại route tìm kiếm và thiết lập định dạng JSON trả về cho Client."
> * **File xử lý:** [EmployeeController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/EmployeeController.php) (Dòng 178-179)

```php
public function ajaxSearch(): void {
    header('Content-Type: application/json; charset=utf-8');
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `public function ajaxSearch(): void {`: Khai báo phương thức xử lý tìm kiếm qua AJAX.
> - `header('Content-Type: application/json; charset=utf-8');`: Gửi header khai báo nội dung trả về là văn bản định dạng JSON sử dụng bộ mã hóa ký tự UTF-8.

#### Bước 3: Lấy tham số tìm kiếm và bộ lọc
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Controller bóc tách dữ liệu tìm kiếm và bộ lọc từ tham số GET gửi lên và làm sạch dữ liệu."
> * **File xử lý:** [EmployeeController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/EmployeeController.php) (Dòng 180-185)

```php
$q          = trim($this->get('q', ''));
$department = trim($this->get('department', ''));
$role       = trim($this->get('role', ''));
$status     = trim($this->get('status', ''));

$filters = compact('q', 'department', 'role', 'status');
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `$q = trim($this->get('q', ''));`: Gọi phương thức `$this->get()` lấy giá trị khóa `q` từ mảng `$_GET['q']` và cắt khoảng trắng.
> - `$department... $role... $status...`: Lấy thông tin từ các bộ lọc select box tương tự.
> - `$filters = compact('q', 'department', 'role', 'status');`: Gộp các biến vừa lấy thành một mảng có các khóa là tên biến thông qua hàm `compact()`.

#### Bước 4: Gọi Model thực hiện truy vấn lọc
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Controller gọi hàm findFiltered của UserModel, truyền mảng các bộ lọc để lấy danh sách nhân viên thỏa mãn điều kiện lọc."
> * **File xử lý:** [EmployeeController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/EmployeeController.php) (Dòng 186)

```php
$employees = $this->userModel->findFiltered($filters, 1, 50);
```

> [!TIP]
> **Giải thích chi tiết dòng code:**
> - `$employees = $this->userModel->findFiltered($filters, 1, 50);`: Gọi hàm lọc nâng cao của UserModel, giới hạn lấy tối đa 50 kết quả của trang đầu tiên.

#### Bước 5: Model thực thi SQL tìm kiếm động
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Model nhận mảng filters, kiểm tra xem có tham số nào thì nối tiếp mệnh đề WHERE SQL LIKE tương ứng vào câu truy vấn và thực thi CSDL."
> * **File xử lý:** [UserModel.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/models/UserModel.php) (Dòng 46-83)

```php
public function findFiltered(array $filters, int $page = 1, int $perPage = 20): array {
    $offset = ($page - 1) * $perPage;
    $sql = "SELECT id, employee_code, full_name, email, role, status, department, position
            FROM users
            WHERE deleted_at IS NULL";
    $params = [];
    if (!empty($filters['q'])) {
        $sql .= " AND (full_name LIKE ? OR email LIKE ? OR employee_code LIKE ?)";
        $like = "%{$filters['q']}%";
        $params[] = $like; $params[] = $like; $params[] = $like;
    }
    $sql .= " ORDER BY full_name LIMIT ? OFFSET ?";
    $params[] = $perPage;
    $params[] = $offset;

    return $this->query($sql, $params);
}
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `$offset = ($page - 1) * $perPage;`: Tính toán số bản ghi cần bỏ qua để phục vụ phân trang dữ liệu.
> - `$sql = "SELECT ... WHERE deleted_at IS NULL";`: Khởi tạo chuỗi truy vấn cơ bản lấy danh sách nhân viên chưa bị xóa mềm.
> - `if (!empty($filters['q'])) { ... }`: Nếu Admin nhập từ khóa tìm kiếm:
>   - `$sql .= " AND (full_name LIKE ? OR email LIKE ? OR employee_code LIKE ?)";`: Ghép thêm mệnh đề tìm kiếm tương đối (`LIKE`) theo Họ tên, Email, hoặc Mã nhân sự.
>   - `$like = "%{$filters['q']}%";`: Định nghĩa chuỗi chứa các ký tự đại diện `%` ở hai đầu.
>   - `$params[] = $like; ...`: Đẩy các tham số tương ứng vào mảng để binding an toàn tránh lỗ hổng bảo mật.
> - `$sql .= " ORDER BY ... LIMIT ? OFFSET ?";`: Nối câu lệnh sắp xếp và phân trang, chạy lệnh `$this->query()` để thực thi truy vấn.

#### Bước 6: Trả kết quả JSON về Client để render lại DOM
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Controller định dạng lại nhãn vai trò/trạng thái và xuất chuỗi dữ liệu dạng JSON. Trình duyệt nhận kết quả và chạy JS vẽ lại bảng HTML hiển thị ngay lập tức."
> * **File xử lý:** [EmployeeController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/EmployeeController.php) (Dòng 188-208)

```php
// PHP Controller định dạng dữ liệu và trả JSON
echo json_encode(['success' => true, 'data' => $result, 'total' => count($result)]);
exit;
```
```javascript
// JavaScript vẽ lại HTML dòng bảng (dùng trong renderEmployeeRows)
let html = '';
employees.forEach(function(emp) {
    html += `<tr id="emp-row-${emp.id}">
        <td>${emp.full_name}</td>
        <td>${emp.email}</td>
        <td>${emp.role_label}</td>
        <td>${emp.department}</td>
    </tr>`;
});
$('table.table tbody').html(html);
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `echo json_encode([...]);`: Sử dụng hàm của PHP chuyển đổi cấu trúc mảng dữ liệu thành một chuỗi văn bản JSON tiêu chuẩn trả về cho Client.
> - `exit;`: Thoát chương trình lập tức để tránh HTML dư thừa của hệ thống bị đính kèm vào dữ liệu JSON.
> - `let html = '';`: Khai báo chuỗi HTML tích lũy rỗng.
> - `employees.forEach(function(emp) { ... });`: Chạy vòng lặp duyệt qua từng đối tượng nhân viên nhận lại từ phản hồi của Server.
> - `html += ...`: Nối chuỗi sinh các hàng mới chứa dữ liệu Họ tên, Email, Vai trò bằng cú pháp Template Literal của JavaScript (`${emp.full_name}`).
> - `$('table.table tbody').html(html);`: Thay thế toàn bộ mã HTML cũ bên trong thân thẻ `<tbody>` bằng chuỗi HTML mới vừa tạo để cập nhật giao diện bất đồng bộ mà không cần reload trang.

---

### 🔒 6. Khóa/Mở khóa tài khoản nhân viên bằng AJAX (`/ajax/employees/toggle-status`)

#### Bước 1: Client gửi dữ liệu (AJAX)
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Khi Admin click nút Khóa/Mở khóa, trình duyệt bắt sự kiện click thông qua Event Delegation (Ủy quyền sự kiện), yêu cầu xác nhận và gửi POST AJAX chứa ID lên Server."
> * **File giao diện:** [index.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/views/employees/index.php)

```javascript
$(document).on('click', '.btn-ajax-toggle', function() {
    const btn = $(this);
    const id  = btn.data('id');
    if (!confirm('Xác nhận thay đổi trạng thái tài khoản này?')) return;

    $.ajax({
        url: BASE_URL + '/ajax/employees/toggle-status',
        method: 'POST',
        data: { id },
        success: function(res) {
            if (res.success) {
                updateStatusUI(id, res.new_status, res.label);
            }
        }
    });
});
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `$(document).on('click', '.btn-ajax-toggle', function() { ... })`: Sử dụng kỹ thuật ủy quyền sự kiện (Event Delegation) lắng nghe click trên toàn bộ `.btn-ajax-toggle`, đảm bảo hoạt động bình thường trên cả các hàng nhân viên vừa được vẽ động qua live search.
> - `const id = btn.data('id');`: Đọc giá trị ID nhân viên được lưu trong thuộc tính `data-id` của thẻ nút.
> - `if (!confirm('...')) return;`: Hiện pop-up xác nhận thao tác, nếu chọn Cancel dừng xử lý.
> - `$.ajax({ ... })`: Phát yêu cầu AJAX bất đồng bộ.
> - `url: BASE_URL + '/ajax/employees/toggle-status'`: Đường dẫn endpoint xử lý.
> - `method: 'POST'`: Phương thức POST dùng để chỉnh sửa/cập nhật tài nguyên.
> - `data: { id }`: Truyền ID nhân sự cần chuyển trạng thái.
> - `updateStatusUI(id, res.new_status, res.label);`: Gọi hàm callback xử lý giao diện khi server cập nhật thành công (như thay đổi màu chấm trạng thái và đổi chữ).

#### Bước 2: Controller bắt request POST
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Tại server, Controller bắt request POST, cài đặt Header JSON và lấy ID nhân sự từ POST."
> * **File xử lý:** [EmployeeController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/EmployeeController.php) (Dòng 213-215)

```php
public function ajaxToggleStatus(): void {
    header('Content-Type: application/json; charset=utf-8');
    $id = (int)$this->post('id');
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `public function ajaxToggleStatus(): void {`: Khai báo phương thức xử lý.
> - `header('Content-Type: application/json; charset=utf-8');`: Đặt định dạng dữ liệu phản hồi là JSON.
> - `$id = (int)$this->post('id');`: Đọc ID nhân sự gửi lên từ POST và ép kiểu số nguyên bảo mật.

#### Bước 3: Validate logic nghiệp vụ khóa tài khoản
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Controller kiểm tra tài khoản có tồn tại không và nghiêm cấm tuyệt đối việc Admin tự khóa tài khoản của chính mình để tránh lỗi mất quyền kiểm soát hệ thống."
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

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `if ($id === Auth::id()) {`: Kiểm tra nếu ID tài khoản cần khóa trùng với ID của Admin hiện đang đăng nhập.
> - `echo json_encode(['success' => false, ...]); exit;`: Nếu trùng, xuất JSON báo lỗi để client biết Admin không được tự khóa chính mình và kết thúc.
> - `$employee = $this->userModel->findById($id);`: Tìm thông tin nhân viên theo ID. Nếu không thấy, báo lỗi không tìm thấy tài khoản và ngắt chương trình.

#### Bước 4: Gọi Model cập nhật trạng thái mới
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Hệ thống đảo ngược trạng thái hoạt động hiện tại (ACTIVE sang LOCKED và ngược lại), sau đó gọi Model lưu trạng thái mới."
> * **File xử lý:** [EmployeeController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/EmployeeController.php) (Dòng 228-229)

```php
$newStatus = $employee['status'] === 'ACTIVE' ? 'LOCKED' : 'ACTIVE';
$this->userModel->updateStatus($id, $newStatus);
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `$newStatus = $employee['status'] === 'ACTIVE' ? 'LOCKED' : 'ACTIVE';`: Đảo ngược trạng thái logic bằng toán tử ba ngôi (nếu đang hoạt động thì chuyển sang khóa, nếu đang khóa thì chuyển sang hoạt động).
> - `$this->userModel->updateStatus($id, $newStatus);`: Gọi Model lưu lại trạng thái mới cập nhật.

#### Bước 5: Model thực thi SQL
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Model chạy câu lệnh SQL UPDATE cập nhật trường status sang trạng thái mới của nhân viên trong CSDL."
> * **File xử lý:** [UserModel.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/models/UserModel.php) (Dòng 229-234)

```php
public function updateStatus(int $id, string $status): void {
    $this->execute(
        "UPDATE users SET status = ?, updated_at = NOW() WHERE id = ?",
        [$status, $id]
    );
}
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `public function updateStatus(int $id, string $status): void {`: Định nghĩa phương thức cập nhật trạng thái trong UserModel.
> - `"UPDATE users SET status = ?, updated_at = NOW() WHERE id = ?"`: Câu SQL UPDATE cập nhật trạng thái mới và cập nhật thời gian chỉnh sửa gần nhất `updated_at`.
> - `[$status, $id]`: Mảng các tham số binding an toàn cho CSDL.

#### Bước 6: Trả kết quả JSON về Client để sửa đổi DOM trực tiếp
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Controller ghi Audit Log, trả dữ liệu JSON thành công. Trình duyệt nhận phản hồi, thay đổi Class và text hiển thị tại hàng nhân sự đó ngay lập tức."
> * **File xử lý:** [EmployeeController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/EmployeeController.php) (Dòng 231-244)

```php
// PHP Controller trả về kết quả
$label = $newStatus === 'LOCKED' ? 'Tạm khóa' : 'Hoạt động';
echo json_encode(['success' => true, 'new_status' => $newStatus, 'label' => $label]);
exit;
```
```javascript
// JS JQuery xử lý thành công (trong hàm updateStatusUI)
const isNowActive = newStatus === 'ACTIVE';
$(`#status-cell-${id}`).html(
    `<span class="status-dot ${isNowActive ? 'active' : 'inactive'}"></span><span>${label}</span>`
);
btn.data('status', newStatus);
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `$label = ...`: Thiết lập nhãn văn bản tiếng Việt thân thiện hiển thị tương ứng trạng thái.
> - `echo json_encode(...); exit;`: Trả về JSON thành công báo cho Client cập nhật DOM.
> - `const isNowActive = newStatus === 'ACTIVE';`: Kiểm tra trạng thái mới xem có hoạt động hay không.
> - `$(`#status-cell-${id}`).html( ... );`: Tìm ô trạng thái trên hàng bảng nhân viên qua ID và chèn HTML cập nhật chấm màu và nhãn trạng thái mới (`active` cho màu xanh lá, `inactive` cho màu đỏ) trực tiếp không reload.
> - `btn.data('status', newStatus);`: Gán lại giá trị status mới vào thuộc tính dữ liệu của nút bấm phục vụ cho lần nhấn kế tiếp.

---

### 🌴 7. Tạo đơn xin nghỉ phép (`/leave/store` - Có kiểm tra trùng lịch)

#### Bước 1: Client gửi dữ liệu
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Nhân viên chọn loại nghỉ phép, điền khoảng ngày từ ngày đến ngày và ghi rõ lý do nghỉ, sau đó gửi yêu cầu tạo đơn phép lên server."
> * **File giao diện:** [create.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/views/leave/create.php)

```html
<form method="POST" action="<?= BASE_URL ?>/leave/store">
    <select name="leave_type" required>
        <option value="ANNUAL">Nghỉ phép năm</option>
        <option value="SICK">Nghỉ ốm</option>
    </select>
    <input type="date" name="start_date" required>
    <input type="date" name="end_date" required>
    <textarea name="reason" required></textarea>
    <button type="submit">Gửi đơn</button>
</form>
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `<form method="POST" action="<?= BASE_URL ?>/leave/store">`: Định nghĩa form gửi POST để tạo đơn xin phép.
> - `<select name="leave_type" required>`: Chọn loại nghỉ (nghỉ phép năm hoặc nghỉ ốm).
> - `<input type="date" name="start_date" required>`: Nhập ngày bắt đầu xin nghỉ phép.
> - `<input type="date" name="end_date" required>`: Nhập ngày kết thúc xin nghỉ phép.
> - `<textarea name="reason" required></textarea>`: Trường nhập văn bản ghi rõ lý do nghỉ phép.
> - `<button type="submit">`: Nút bấm submit gửi toàn bộ đơn phép lên.

#### Bước 2: Controller bắt request POST
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Tại server, LeaveController tiếp nhận request POST, lấy thông tin đăng nhập của nhân viên và các trường thông tin ngày tháng từ POST."
> * **File xử lý:** [LeaveController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/LeaveController.php) (Dòng 49-54)

```php
public function store(): void {
    $employeeId = Auth::id();
    $startDate  = $this->post('start_date');
    $endDate    = $this->post('end_date');
    $leaveType  = $this->post('leave_type');
    $reason     = trim($this->post('reason', ''));
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `public function store(): void {`: Khai báo phương thức lưu đơn nghỉ phép.
> - `$employeeId = Auth::id();`: Đọc ID nhân sự đang gửi đơn từ Session.
> - `$startDate = $this->post('start_date');`: Lấy ngày bắt đầu xin nghỉ từ POST.
> - `$endDate = $this->post('end_date');`: Lấy ngày kết thúc xin nghỉ từ POST.
> - `$leaveType = $this->post('leave_type');`: Lấy phân loại nghỉ phép.
> - `$reason = trim($this->post('reason', ''));`: Đọc lý do nghỉ và làm sạch khoảng trắng thừa hai đầu.

#### Bước 3: Validate dữ liệu và kiểm tra trùng lịch (Overlap Check)
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Controller kiểm tra tính hợp lệ về logic thời gian và gọi Model kiểm tra xem nhân viên này có đơn nghỉ nào khác bị trùng lịch ngày trong khoảng này hay chưa."
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
    $this->render('leave/create', ['errors' => $errors, 'old' => $_POST]);
    return;
}
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `if (!$startDate || !$endDate) ...`: Kiểm tra nếu thiếu ngày tháng xin phép, báo lỗi.
> - `if ($startDate > $endDate) ...`: Kiểm tra tính hợp lệ: ngày bắt đầu bắt buộc phải trước hoặc bằng ngày kết thúc.
> - `if (strlen($reason) < 5) ...`: Bắt buộc lý do phải tối thiểu 5 ký tự để tránh lý do rác.
> - `if (empty($errors) && $this->leaveModel->hasOverlap($employeeId, $startDate, $endDate)) {`: Nếu không có lỗi cơ bản, gọi hàm `hasOverlap` của Model kiểm tra trùng khoảng ngày nghỉ phép trong CSDL. Nếu có đơn phép khác của nhân sự này đã duyệt hoặc chờ duyệt trùng lịch:
> - `$errors[] = 'Bạn đã có đơn nghỉ trùng khoảng thời gian này.';`: Báo lỗi trùng ngày nghỉ.
> - `if (!empty($errors)) { ... }`: Nếu có lỗi, tải lại trang thêm kèm lỗi hiển thị và trả lại dữ liệu đã điền (`old`), thoát hàm.

#### Bước 4: Gọi Model lưu trữ đơn
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Nếu vượt qua kiểm tra trùng lịch, Controller gọi Model LeaveRequestModel để chèn đơn phép mới vào DB."
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

> [!TIP]
> **Giải thích chi tiết dòng code:**
> - `$this->leaveModel->create([ ... ]);`: Gọi phương thức `create` của Model truyền vào mảng thông tin đơn phép để thực hiện lưu trữ.

#### Bước 5: Model thực thi SQL
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Model chạy câu lệnh SQL INSERT chèn đơn phép mới vào bảng leave_requests, trạng thái mặc định của đơn là PENDING."
> * **File xử lý:** [LeaveRequestModel.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/models/LeaveRequestModel.php) (Dòng 9-22)

```php
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

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `public function create(array $data): int {`: Phương thức nhận mảng dữ liệu và trả về ID tự sinh kiểu số nguyên.
> - `"INSERT INTO leave_requests ... VALUES (..., 'PENDING', ...)"`: SQL INSERT chèn thông tin đơn xin nghỉ phép vào bảng. Đơn phép mới chèn luôn mặc định có trạng thái là `'PENDING'` (Chờ duyệt) và trường thời gian tạo được tự lấy giờ hiện tại `NOW()`.
> - `[ ... ]`: Các trường tham số thực tế dùng để binding vào câu SQL.

#### Bước 6: Trả kết quả về Client
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Hệ thống ghi nhận flash thông báo thành công và chuyển hướng trình duyệt của nhân sự quay lại trang quản lý lịch sử đơn nghỉ phép."
> * **File xử lý:** [LeaveController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/LeaveController.php) (Dòng 82-83)

```php
Session::flash('success', 'Gửi đơn nghỉ phép thành công. Chờ duyệt.');
$this->redirect('/leave');
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `Session::flash('success', 'Gửi đơn nghỉ phép thành công. Chờ duyệt.');`: Lưu thông báo gửi đơn thành công vào Session tạm thời.
> - `$this->redirect('/leave');`: Chuyển hướng người dùng quay lại trang quản lý danh sách đơn xin nghỉ phép cá nhân.

---

### 🌴 8. Duyệt đơn nghỉ phép & tự động hủy lịch trực bằng AJAX (`/ajax/leave/approve` - Admin Only)

#### Bước 1: Client gửi dữ liệu (AJAX)
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Admin click Duyệt đơn phép, JS lắng nghe sự kiện, vô hiệu hóa nút bấm tránh gửi đúp và gửi AJAX POST chứa ID đơn phép lên server."
> * **File giao diện:** [index.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/views/leave/index.php)

```javascript
$(document).on('click', '.btn-ajax-approve', function() {
    const btn = $(this);
    const id  = btn.data('id');
    if (!confirm('Duyệt đơn nghỉ phép này?')) return;
    btn.prop('disabled', true);

    $.ajax({
        url: BASE_URL + '/ajax/leave/approve',
        method: 'POST',
        data: { id },
        success: function(res) {
            if (res.success) {
                updateRowToApproved(btn);
            }
        }
    });
});
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `$(document).on('click', '.btn-ajax-approve', function() { ... })`: Lắng nghe click chuột trên nút Duyệt phép `.btn-ajax-approve`.
> - `const id = btn.data('id');`: Lấy ID đơn nghỉ phép được gán ở thuộc tính `data-id`.
> - `if (!confirm('...')) return;`: Hiện hộp thoại xác nhận của trình duyệt trước khi duyệt.
> - `btn.prop('disabled', true);`: Vô hiệu hóa nút duyệt ngay sau khi chọn OK, ngăn chặn Admin nhấp chuột nhiều lần gửi các request trùng lặp làm phát sinh lỗi dữ liệu.
> - `$.ajax({ ... })`: Khởi động yêu cầu AJAX bất đồng bộ.
> - `url: BASE_URL + '/ajax/leave/approve'`: Endpoint nhận xử lý duyệt phép.
> - `method: 'POST'`: Phương thức POST để ghi đè cập nhật trạng thái đơn.
> - `updateRowToApproved(btn);`: Hàm callback JQuery cập nhật lại UI hàng đơn phép đó khi Server phản hồi thành công.

#### Bước 2: Controller bắt request POST
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Tại server, Controller tiếp nhận yêu cầu POST để duyệt đơn, đặt header định dạng JSON và lấy ID đơn phép."
> * **File xử lý:** [LeaveController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/LeaveController.php) (Dòng 171-173)

```php
public function ajaxApprove(): void {
    header('Content-Type: application/json; charset=utf-8');
    $id    = (int)$this->post('id');
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `public function ajaxApprove(): void {`: Khai báo phương thức xử lý AJAX duyệt đơn nghỉ phép.
> - `header('Content-Type: application/json; charset=utf-8');`: Đặt định dạng trả về là JSON.
> - `$id = (int)$this->post('id');`: Đọc ID đơn phép gửi từ POST và ép kiểu số nguyên.

#### Bước 3: Validate dữ liệu đơn phép
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Controller kiểm tra xem đơn nghỉ phép có tồn tại trong hệ thống hay không và bắt buộc phải đang ở trạng thái PENDING mới được duyệt."
> * **File xử lý:** [LeaveController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/LeaveController.php) (Dòng 174-180)

```php
$leave = $this->leaveModel->findById($id);
if (!$leave || $leave['status'] !== 'PENDING') {
    echo json_encode(['success' => false, 'message' => 'Đơn không hợp lệ hoặc đã được xử lý.']);
    exit;
}
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `$leave = $this->leaveModel->findById($id);`: Lấy thông tin chi tiết đơn nghỉ phép từ Database theo ID.
> - `if (!$leave || $leave['status'] !== 'PENDING') { ... }`: Kiểm tra nếu đơn nghỉ phép không tồn tại HOẶC trạng thái của đơn đã khác `'PENDING'` (tức đã được duyệt hoặc từ chối trước đó rồi).
> - `echo json_encode([...]); exit;`: Trả về JSON báo lỗi đơn không hợp lệ hoặc đã được duyệt rồi và kết thúc sớm script.

#### Bước 4: Gọi Model Duyệt đơn & Tự động hủy lịch trực trùng ngày
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Controller gọi Model phê duyệt trạng thái đơn thành APPROVED, đồng thời tự động gọi Model chấm công để hủy toàn bộ ca trực của nhân viên bị trùng lịch trong những ngày nghỉ phép này."
> * **File xử lý:** [LeaveController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/LeaveController.php) (Dòng 181-186)

```php
$employeeId = (int)$leave['employee_id'];
$this->leaveModel->approve($id, Auth::id()); 

$removed = $this->workModel->removeAssignmentsByEmployeeInRange(
    $employeeId, $leave['start_date'], $leave['end_date'],
    'AUTO_REMOVED_DUE_TO_APPROVED_LEAVE'
);
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `$employeeId = (int)$leave['employee_id'];`: Lấy ID của nhân viên sở hữu đơn nghỉ phép này.
> - `$this->leaveModel->approve($id, Auth::id());`: Gọi Model cập nhật trạng thái đơn phép sang Approved và lưu ID của Admin duyệt đơn vào trường `reviewed_by`.
> - `$removed = $this->workModel->removeAssignmentsByEmployeeInRange( ... );`: Gọi Model lịch trực để hủy (gỡ bỏ) các ca làm việc đã phân công của nhân sự này trong khoảng ngày xin nghỉ từ `start_date` đến `end_date`. Lý do hủy tự động được ghi nhận là `"AUTO_REMOVED..."`.

#### Bước 5: Model thực thi SQL cập nhật dữ liệu
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Model chạy hai lệnh SQL UPDATE: Một lệnh cập nhật trạng thái đơn nghỉ thành APPROVED, một lệnh cập nhật trạng thái ca trực trùng ngày sang REMOVED."
> * **File xử lý:** [LeaveRequestModel.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/models/LeaveRequestModel.php) & [WorkRecordModel.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/models/WorkRecordModel.php)

```php
// 1. Trong LeaveRequestModel::approve
$this->execute(
    "UPDATE leave_requests SET status = 'APPROVED', reviewed_by = ?, reviewed_at = NOW(), updated_at = NOW() WHERE id = ?",
    [$reviewedBy, $id]
);

// 2. Trong WorkRecordModel::removeAssignment (gỡ ca trùng ngày)
$this->execute(
    "UPDATE work_records SET record_status = 'REMOVED', meta_json = ?, updated_at = NOW() WHERE id = ?",
    [json_encode($meta), $assignmentId]
);
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - Lệnh SQL 1: Thực hiện UPDATE trong bảng `leave_requests` chuyển trạng thái sang `'APPROVED'`, ghi nhận Admin duyệt (`reviewed_by`) và thời điểm duyệt (`reviewed_at`).
> - Lệnh SQL 2: Thực hiện UPDATE trong bảng `work_records` tìm bản ghi lịch trực bị trùng ngày, đổi trạng thái cột `record_status` sang `'REMOVED'` để giải phóng ca trực, và cập nhật thông tin lý do gỡ tự động vào chuỗi JSON trường `meta_json`.

#### Bước 6: Trả kết quả JSON về cho Client cập nhật giao diện
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Controller ghi Audit Log, trả dữ liệu JSON thành công. Trình duyệt bắt kết quả và đổi màu badge tại hàng đó sang APPROVED mà không cần reload trang."
> * **File xử lý:** [LeaveController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/LeaveController.php) (Dòng 188-201)

```php
// PHP Controller trả về kết quả
echo json_encode(['success' => true, 'message' => 'Đã duyệt đơn nghỉ phép.']);
exit;
```
```javascript
// JS JQuery xử lý thành công (trong hàm updateRowToApproved)
const row = btn.closest('tr');
row.find('.badge').removeClass().addClass('badge badge-approved').text('Đã duyệt');
row.find('td:last-child').html('<span class="text-muted text-sm">—</span>');
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `echo json_encode(['success' => true, ...]); exit;`: Trả về kết quả JSON báo duyệt thành công đơn và thoát script PHP.
> - `const row = btn.closest('tr');`: JavaScript JQuery tìm thẻ hàng bảng cha `<tr>` chứa nút duyệt vừa nhấn.
> - `row.find('.badge').removeClass().addClass('badge badge-approved').text('Đã duyệt');`: Tìm thẻ hiển thị trạng thái của hàng (`.badge`), loại bỏ các CSS class cũ và thêm class mới `badge-approved` (màu xanh lá) và đổi chữ hiển thị thành "Đã duyệt".
> - `row.find('td:last-child').html('<span ...>—</span>');`: Thay thế các nút thao tác "Duyệt" và "Từ chối" ở cột cuối cùng thành dấu gạch ngang vì đơn đã được duyệt xong, tránh bấm lại.

---

### ⏰ 9. Chỉnh sửa chấm công (`/ajax/attendance/update` - AJAX, tự tính lại số phút đi làm)

#### Bước 1: Client gửi dữ liệu (AJAX)
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Admin bấm nút sửa công, điền giờ vào/ra, lý do sửa vào Modal. Khi nhấn Lưu, AJAX POST gửi thông tin cập nhật lên Server."
> * **File giao diện:** [index.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/views/attendance/index.php)

```javascript
$('#btnSaveAttendance').on('click', function() {
    const checkIn  = $('#editCheckIn').val();
    const checkOut = $('#editCheckOut').val();
    const note     = $('#editNote').val().trim();

    if (!checkIn || !checkOut || !note) return;

    $.ajax({
        url: BASE_URL + '/ajax/attendance/update',
        method: 'POST',
        data: {
            id: currentAttId,
            check_in_at: checkIn,
            check_out_at: checkOut,
            late_minutes: $('#editLate').val() || 0,
            overtime_minutes: $('#editOvertime').val() || 0,
            note: note
        },
        success: function(res) {
            if (res.success) {
                const tr = $(`tr[data-att-id="${currentAttId}"]`);
                tr.find('.att-checkin').text(res.check_in);
                tr.find('.att-checkout').text(res.check_out);
                tr.find('.att-worked').text(res.worked_hours + ' giờ');
                closeEditModal();
            }
        }
    });
});
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `$('#btnSaveAttendance').on('click', function() { ... })`: Lắng nghe sự kiện click trên nút "Lưu" chấm công tại giao diện Modal sửa.
> - `const checkIn = $('#editCheckIn').val(); ...`: Đọc các giá trị ngày giờ check-in, check-out và lý do chỉnh sửa từ các ô nhập trong Modal.
> - `if (!checkIn || !checkOut || !note) return;`: Nếu thiếu thông tin bắt buộc, dừng xử lý ngay tại client.
> - `url: BASE_URL + '/ajax/attendance/update'`: Endpoint cập nhật chấm công.
> - `data: { id: currentAttId, ... }`: Truyền ID bản ghi chấm công cần chỉnh sửa, thời gian check-in/out, số phút đi muộn/tăng ca và lý do ghi nhận.
> - `success: function(res) { ... }`: Khi server cập nhật thành công CSDL:
>   - `const tr = $(`tr[data-att-id="${currentAttId}"]`);`: Tìm hàng bảng `<tr>` của bản ghi chấm công vừa sửa dựa trên thuộc tính `data-att-id`.
>   - `tr.find('.att-checkin').text(res.check_in); ...`: Cập nhật lại chuỗi hiển thị giờ vào và giờ ra mới được server định dạng lại.
>   - `tr.find('.att-worked').text(res.worked_hours + ' giờ');`: Điền số giờ làm việc thực tế mới được server tính toán lại tự động.
>   - `closeEditModal();`: Gọi hàm JS ẩn Modal chỉnh sửa chấm công.

#### Bước 2: Controller bắt request POST
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Tại server, Controller tiếp nhận request POST cập nhật chấm công của nhân sự, đặt header JSON và lấy ID bản ghi."
> * **File xử lý:** [AttendanceController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/AttendanceController.php) (Dòng 100-102)

```php
public function ajaxUpdate(): void {
    header('Content-Type: application/json; charset=utf-8');
    $id  = (int)$this->post('id');
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `public function ajaxUpdate(): void {`: Khai báo phương thức xử lý AJAX cập nhật chấm công.
> - `header('Content-Type: application/json; charset=utf-8');`: Đặt định dạng trả về là JSON.
> - `$id = (int)$this->post('id');`: Đọc ID bản ghi chấm công từ mảng POST và ép kiểu số nguyên an toàn.

#### Bước 3: Validate và tự động tính toán lại số phút làm việc thực tế
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Controller kiểm tra bản ghi tồn tại và bắt buộc nhập lý do sửa. Hệ thống tự động tính lại số phút làm việc bằng cách lấy hiệu số giây trừ đi 30 phút nghỉ trưa."
> * **File xử lý:** [AttendanceController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/AttendanceController.php) (Dòng 103-124)

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

$workedMin = (int)((strtotime($checkOut) - strtotime($checkIn)) / 60 - ($row['break_minutes'] ?? 30));
$workedMin = max(0, $workedMin);
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `$row = $this->workModel->getAttendanceById($id);`: Truy vấn lấy thông tin chấm công hiện tại từ CSDL.
> - `if (!$row) { ... }`: Trả lỗi JSON nếu không tìm thấy bản ghi chấm công trong CSDL.
> - `$checkIn = ... $checkOut = ... $note = ...`: Lọc và nhận các giá trị đầu vào gửi lên từ POST.
> - `if (!$checkIn || !$checkOut) { ... }`: Nếu thiếu giờ vào hoặc giờ ra, báo lỗi và dừng script PHP.
> - `if (empty($note)) { ... }`: Nếu không điền lý do chỉnh sửa (bắt buộc), trả lỗi JSON và kết thúc.
> - `strtotime($checkOut) - strtotime($checkIn)`: Chuyển đổi định dạng chuỗi ngày giờ check-in và check-out thành dạng số giây (Unix Timestamp) rồi trừ hiệu số để ra tổng số giây làm việc của nhân viên trong ngày.
> - `/ 60`: Chia cho 60 để đổi giây sang phút.
> - `- ($row['break_minutes'] ?? 30)`: Trừ đi số phút nghỉ giữa ca (mặc định là 30 phút nghỉ trưa) để ra số phút làm việc thực tế, đảm bảo tính công bằng.
> - `$workedMin = max(0, $workedMin);`: Đảm bảo nếu xảy ra lỗi nhập liệu làm số phút âm, giá trị gán trả về sẽ giữ mức tối thiểu là 0.

#### Bước 4: Gọi Model cập nhật bản ghi công
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Controller gọi Model để cập nhật thời gian check-in/out mới, số phút làm việc thực tế đã tính lại, số phút đi muộn, tăng ca và lý do sửa đổi."
> * **File xử lý:** [AttendanceController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/AttendanceController.php) (Dòng 126-133)

```php
$this->workModel->updateAttendance($id, [
    'check_in_at'      => $checkIn,
    'check_out_at'     => $checkOut,
    'worked_minutes'   => $workedMin,
    'late_minutes'     => (int)$this->post('late_minutes', 0),
    'overtime_minutes' => (int)$this->post('overtime_minutes', 0),
    'note'             => $note,
]);
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `$this->workModel->updateAttendance($id, [ ... ]);`: Gọi hàm cập nhật dữ liệu của Model truyền vào ID và mảng thông tin chấm công mới, ép kiểu số nguyên cho các trường phút đi muộn và tăng ca.

#### Bước 5: Model thực thi SQL cập nhật dữ liệu
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Model chạy câu lệnh SQL UPDATE cập nhật lại toàn bộ các trường thời gian tương ứng trong bản ghi bảng công."
> * **File xử lý:** [WorkRecordModel.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/models/WorkRecordModel.php) (Dòng 368-385)

```php
public function updateAttendance(int $id, array $data): void {
    $this->execute(
        "UPDATE work_records
         SET check_in_at = ?, check_out_at = ?, worked_minutes = ?,
             late_minutes = ?, overtime_minutes = ?, note = ?, updated_at = NOW()
         WHERE id = ?",
        [
            $data['check_in_at'], $data['check_out_at'], $data['worked_minutes'],
            $data['late_minutes'], $data['overtime_minutes'], $data['note'], $id
        ]
    );
}
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `public function updateAttendance(int $id, array $data): void {`: Khai báo phương thức cập nhật chấm công trong Model.
> - `"UPDATE work_records SET ... WHERE id = ?"`: Câu lệnh UPDATE SQL cập nhật các trường giờ vào/ra, số phút làm thực tế, đi muộn, tăng ca, lý do chỉnh sửa ghi chú và cập nhật thời gian sửa đổi `updated_at`.
> - `[ $data['check_in_at'], ... $id ]`: Mảng chứa các tham số truyền tương ứng cho các placeholder `?` của PDO để bảo mật truy vấn dữ liệu.

#### Bước 6: Trả kết quả JSON về Client để cập nhật giao diện
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Controller ghi lịch sử Audit Log, trả dữ liệu JSON chứa định dạng giờ rút gọn và tổng số giờ làm thực tế để JS cập nhật hiển thị tức thời."
> * **File xử lý:** [AttendanceController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/AttendanceController.php) (Dòng 135-153)

```php
$this->sysModel->writeAudit(['action' => 'UPDATE_ATTENDANCE', ...]);
echo json_encode([
    'success'      => true,
    'message'      => 'Đã cập nhật chấm công.',
    'check_in'     => date('H:i', strtotime($checkIn)),
    'check_out'    => date('H:i', strtotime($checkOut)),
    'worked_hours' => round($workedMin / 60, 1),
]);
exit;
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `$this->sysModel->writeAudit([ ... ])`: Ghi log kiểm toán lưu trữ lại thông tin người thực hiện, bản ghi chấm công cũ và mới trước khi thay đổi để phục vụ việc giám sát hoạt động sửa công của Admin.
> - `date('H:i', strtotime($checkIn))`: Định dạng lại giờ vào chỉ hiển thị dạng "Giờ:Phút" (ví dụ `'08:00'`) giúp giao diện bảng hiển thị gọn gàng.
> - `round($workedMin / 60, 1)`: Quy đổi tổng số phút làm việc thực tế sang đơn vị giờ (bằng cách chia cho 60) và làm tròn đến 1 chữ số thập phân (ví dụ `8.5` giờ) để trả về client.
> - `echo json_encode([...]); exit;`: Trả về JSON thành công để JS cập nhật lại các ô văn bản trên DOM và thoát.

---

### 💵 10. Tính lương tự động (`/payroll/calculate`)

#### Bước 1: Client gửi dữ liệu
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Admin chọn Kỳ lương và bấm nút tính lương. Trình duyệt gửi request POST chứa ID kỳ lương lên máy chủ."
> * **File giao diện:** `app/views/payroll/index.php`

```html
<form method="POST" action="<?= BASE_URL ?>/payroll/calculate">
    <input type="hidden" name="period_id" value="<?= $period['id'] ?>">
    <button type="submit">Tính toán lương</button>
</form>
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `<form method="POST" action="<?= BASE_URL ?>/payroll/calculate">`: Khai báo form gửi POST đến route tính lương tự động.
> - `<input type="hidden" name="period_id" value="<?= $period['id'] ?>">`: Thẻ input ẩn gửi kèm ID của Kỳ lương cụ thể cần tính toán (ví dụ: ID kỳ lương tháng 05/2026).
> - `<button type="submit">`: Nút gửi request kích hoạt tính lương.

#### Bước 2: Controller bắt request POST
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Tại server, bộ điều khiển PayrollController bắt nhận request POST để tiến hành tính toán tự động."
> * **File xử lý:** [PayrollController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/PayrollController.php) (Dòng 76-79)

```php
public function calculate(): void {
    $periodId = (int)$this->post('period_id');
    $period   = $this->payModel->getPeriodById($periodId);
    if (!$period) $this->abort(404);
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `public function calculate(): void {`: Khai báo phương thức xử lý tính lương.
> - `$periodId = (int)$this->post('period_id');`: Đọc ID kỳ lương gửi từ POST lên và ép kiểu số nguyên để bảo mật.
> - `$period = $this->payModel->getPeriodById($periodId);`: Gọi Model lấy thông tin thời gian bắt đầu và kết thúc của kỳ lương này.
> - `if (!$period) $this->abort(404);`: Nếu không tồn tại kỳ lương, trả lỗi 404.

#### Bước 3: Lấy danh sách nhân viên & Tính toán công thức lương nghiệp vụ
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Hệ thống lấy toàn bộ nhân viên, duyệt qua từng người để tổng hợp số phút làm việc, đi muộn, tăng ca rồi áp dụng công thức tính lương."
> * **File xử lý:** [PayrollController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/PayrollController.php) (Dòng 81-113)

```php
$employees = $this->userModel->findAll();

foreach ($employees as $emp) {
    $summary = $this->workModel->getAttendanceSummary(
        (int)$emp['id'],
        $period['period_start'],
        $period['period_end']
    );

    $hourlyRate    = (float)$emp['hourly_rate'];
    $workedMin     = (int)$summary['total_worked'];
    $overtimeMin   = (int)$summary['total_overtime'];
    $lateMin       = (int)$summary['total_late'];

    $baseAmount      = round(($workedMin / 60) * $hourlyRate, 2);
    $overtimeAmount  = round(($overtimeMin / 60) * $hourlyRate * 1.5, 2);
    $deductionAmount = round(($lateMin / 60) * $hourlyRate * 0.5, 2);
    $finalAmount     = $baseAmount + $overtimeAmount - $deductionAmount;

    $snapshot = [
        'employee_id'      => $emp['id'],
        'hourly_rate'      => $hourlyRate,
        'worked_minutes'   => $workedMin,
        'overtime_minutes' => $overtimeMin,
        'late_minutes'     => $lateMin,
        'calculated_at'    => date('Y-m-d H:i:s'),
    ];
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `$employees = $this->userModel->findAll();`: Lấy toàn bộ nhân sự đang hoạt động trong hệ thống.
> - `foreach ($employees as $emp) { ... }`: Vòng lặp duyệt qua từng nhân sự để tính lương.
> - `$summary = $this->workModel->getAttendanceSummary( ... );`: Gọi Model lấy thông tin tổng hợp chấm công của nhân sự đó từ ngày bắt đầu đến ngày kết thúc kỳ lương.
> - `$hourlyRate = (float)$emp['hourly_rate'];`: Đọc lương mỗi giờ của nhân sự đó và chuyển sang kiểu float.
> - `$workedMin = (int)$summary['total_worked']; ...`: Trích xuất tổng số phút làm việc chuẩn, tổng số phút tăng ca và số phút đi muộn trong kỳ lương.
> - `$baseAmount = round(($workedMin / 60) * $hourlyRate, 2);`: **Công thức lương chuẩn:** đổi số phút làm việc sang giờ (`$workedMin / 60`), nhân với mức lương mỗi giờ, làm tròn lấy 2 chữ số thập phân (`round(..., 2)`).
> - `$overtimeAmount = round(($overtimeMin / 60) * $hourlyRate * 1.5, 2);`: **Công thức tăng ca:** đổi số phút tăng ca sang giờ, nhân với mức lương mỗi giờ và nhân với **hệ số tăng ca 1.5** (overtime rate).
> - `$deductionAmount = round(($lateMin / 60) * $hourlyRate * 0.5, 2);`: **Công thức đi muộn:** đổi số phút đi muộn sang giờ, nhân với mức lương mỗi giờ và phạt **trừ 50% mức lương giờ** tương ứng.
> - `$finalAmount = $baseAmount + $overtimeAmount - $deductionAmount;`: **Tổng lương thực nhận:** bằng Lương cơ bản + Lương tăng ca - Khoản phạt đi muộn.
> - `$snapshot = [ ... ];`: Tạo mảng snapshot lưu giữ lại toàn bộ tham số lương và công tại thời điểm tính toán để phục vụ đối chiếu lịch sử sau này.

#### Bước 4: Gọi Model lưu trữ kết quả tính lương
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Sau khi tính toán xong cho mỗi nhân viên, Controller gọi Model PayrollModel để tiến hành lưu trữ phiếu lương chi tiết của từng người."
> * **File xử lý:** [PayrollController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/PayrollController.php) (Dòng 115-127)

```php
    $this->payModel->upsertItem([
        'period_id'        => $periodId,
        'employee_id'      => (int)$emp['id'],
        'period_start'     => $period['period_start'],
        'period_end'       => $period['period_end'],
        'base_amount'      => $baseAmount,
        'overtime_amount'  => $overtimeAmount,
        'allowance_amount' => 0.0,
        'deduction_amount' => $deductionAmount,
        'final_amount'     => $finalAmount,
        'snapshot'         => $snapshot,
        'created_by'       => Auth::id(),
    ]);
}
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `$this->payModel->upsertItem([ ... ]);`: Gọi phương thức lưu trữ của Model. Dữ liệu bao gồm các thông số số tiền, ngày tháng và snapshot. Dấu đóng ngoặc nhọn `}` kết thúc vòng lặp tính toán cho từng nhân sự.

#### Bước 5: Model thực thi SQL
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Model kiểm tra xem nhân viên đã có phiếu lương trong kỳ này chưa (qua câu lệnh SELECT). Nếu đã tồn tại thì tiến hành chạy UPDATE cập nhật lại số tiền lương mới, nếu chưa có thì chạy INSERT chèn mới phiếu lương."
> * **File xử lý:** [PayrollModel.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/models/PayrollModel.php) (Dòng 58-110)

```php
public function upsertItem(array $data): void {
    $existing = $this->queryOne(
        "SELECT id FROM payroll_records WHERE record_type = 'ITEM' AND parent_id = ? AND employee_id = ?",
        [$data['period_id'], $data['employee_id']]
    );
    $snapshot = json_encode($data['snapshot'] ?? []);

    if ($existing) {
        $this->execute(
            "UPDATE payroll_records
             SET base_amount = ?, overtime_amount = ?, deduction_amount = ?, final_amount = ?,
                 calculation_snapshot_json = ?, status = 'READY', updated_at = NOW()
             WHERE id = ?",
            [$data['base_amount'], $data['overtime_amount'], $data['deduction_amount'], $data['final_amount'], $snapshot, (int)$existing['id']]
        );
    } else {
        $this->execute(
            "INSERT INTO payroll_records (record_type, parent_id, employee_id, period_start, period_end, base_amount, overtime_amount, deduction_amount, final_amount, calculation_snapshot_json, status, created_by, created_at, updated_at)
             VALUES ('ITEM', ?, ?, ?, ?, ?, ?, ?, ?, ?, 'READY', ?, NOW(), NOW())",
            [$data['period_id'], $data['employee_id'], $data['period_start'], $data['period_end'], $data['base_amount'], $data['overtime_amount'], $data['deduction_amount'], $data['final_amount'], $snapshot, $data['created_by']]
        );
    }
}
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `public function upsertItem(array $data): void {`: Định nghĩa phương thức upsert (update hoặc insert).
> - `$existing = $this->queryOne( ... );`: Tìm xem nhân sự này đã có phiếu lương trong kỳ này lưu trong bảng `payroll_records` chưa.
> - `$snapshot = json_encode( ... );`: Chuyển mảng snapshot sang dạng chuỗi JSON để lưu trữ gọn gàng vào duy nhất một cột CSDL.
> - `if ($existing) { ... }`: Nếu đã tồn tại bản ghi, thực thi câu lệnh SQL UPDATE cập nhật lại toàn bộ các cột số tiền lương mới tính, trạng thái đặt lại thành `'READY'` để chuẩn bị phát hành.
> - `else { ... }`: Nếu chưa có bản ghi, thực thi câu lệnh SQL INSERT chèn mới một dòng thông tin phiếu lương chi tiết cho nhân sự này với trạng thái mặc định là `'READY'`.

#### Bước 6: Cập nhật trạng thái kỳ lương và chuyển hướng
> [!IMPORTANT]
> * **Lời dẫn học thuộc:** "Sau khi tính toán xong cho toàn bộ nhân sự, Controller cập nhật trạng thái của Kỳ lương thành CALCULATED (Đã tính), ghi nhận log và chuyển hướng Admin quay lại trang bảng lương kèm thông báo thành công."
> * **File xử lý:** [PayrollController.php](file:///d:/PTIT/NAM3/KI-2/LTW/PHP/app/controllers/PayrollController.php) (Dòng 130-142)

```php
$this->payModel->updatePeriodStatus($periodId, 'CALCULATED');
$this->sysModel->writeAudit(['action' => 'CALCULATE_PAYROLL', ...]);

Session::flash('success', 'Đã tính xong lương. Vui lòng kiểm tra trước khi công bố.');
$this->redirect('/payroll');
```

> [!TIP]
> **Giải thích chi tiết từng dòng code:**
> - `$this->payModel->updatePeriodStatus($periodId, 'CALCULATED');`: Gọi Model cập nhật trạng thái kỳ lương cha sang trạng thái `'CALCULATED'` (Đã tính toán xong), khóa dữ liệu không cho chỉnh sửa tự do.
> - `$this->sysModel->writeAudit([ ... ])`: Ghi log kiểm toán thông báo Admin đã chạy tính lương cho toàn bộ nhân sự trong kỳ.
> - `Session::flash('success', ...)`: Lưu thông điệp tính toán lương thành công vào Session flash.
> - `$this->redirect('/payroll');`: Chuyển hướng người dùng quay lại màn hình chính quản lý bảng lương để kiểm tra chi tiết phiếu lương của từng nhân viên.
