# TÀI LIỆU ĐẶC TẢ HỆ THỐNG

## AI-POWERED HR MANAGEMENT SYSTEM

## 1. Mục tiêu tài liệu

Tài liệu này mô tả đầy đủ phạm vi, kiến trúc, dữ liệu, luồng nghiệp vụ, ràng buộc hệ thống, cách tích hợp AI và lộ trình triển khai của hệ thống quản lý nhân sự có hỗ trợ AI. Mục tiêu là để một nhóm phát triển có thể dùng trực tiếp tài liệu này làm nền tảng triển khai từ đầu đến cuối mà không phải suy diễn quá nhiều.

Tài liệu được viết theo hướng:

- Đủ chi tiết để đội backend, frontend, database và QA cùng làm việc.
- Tập trung vào tính khả thi triển khai, không chỉ dừng ở ý tưởng.
- Phân tách rõ phần bắt buộc của giai đoạn đầu và phần mở rộng về sau.

---

## 2. Mục tiêu hệ thống

Hệ thống được xây dựng để giải quyết 5 bài toán cốt lõi trong quản trị nhân sự:

1. Quản lý hồ sơ và tài khoản nhân viên tập trung.
2. Tổ chức đăng ký và phân ca làm việc minh bạch, có kiểm soát ràng buộc.
3. Quản lý nghỉ phép, chấm công, tính lương và khiếu nại trên cùng một nền tảng.
4. Ghi nhận đầy đủ lịch sử thao tác để dễ đối chiếu và chống gian lận.
5. Khai thác AI để hỗ trợ phân tích nhân sự và gợi ý vận hành, nhưng không thay thế quyết định của quản trị viên.

### 2.1. Kết quả mong muốn

Sau khi triển khai, hệ thống phải giúp doanh nghiệp:

- Giảm thao tác thủ công trong xếp lịch và tính lương.
- Hạn chế sai sót do nhập liệu, chồng chéo ca làm hoặc duyệt nghỉ phép không đồng bộ.
- Tăng khả năng truy vết khi phát sinh tranh chấp lương, lịch hoặc hiệu suất.
- Có dashboard quản trị đủ dữ liệu để ra quyết định vận hành.

### 2.2. Đối tượng sử dụng

- Nhân viên: dùng để cập nhật hồ sơ, đăng ký ca, xin nghỉ, xem lương, gửi khiếu nại.
- Quản trị viên: dùng để quản lý nhân sự, duyệt đơn, xếp lịch, tính lương, xử lý khiếu nại, xem phân tích AI.

### 2.3. Phạm vi giai đoạn MVP

Các chức năng bắt buộc cần có trong phiên bản đầu tiên:

- Đăng nhập, phân quyền, quản lý hồ sơ người dùng.
- Quản lý ca làm và lịch làm việc theo tuần.
- Quản lý nghỉ phép với phê duyệt nhiều trạng thái.
- Quản lý dữ liệu chấm công.
- Tính lương theo kỳ.
- Hệ thống khiếu nại liên quan đến lịch và lương.
- Audit log cho mọi thao tác quan trọng.
- Dashboard AI cho quản trị viên với 3 module: cảnh báo kiệt sức, gợi ý xếp lịch, báo cáo tuần tự động.

### 2.4. Ngoài phạm vi giai đoạn 1

Để đảm bảo khả thi, các hạng mục sau không bắt buộc ở giai đoạn đầu:

- Ứng dụng mobile native.
- Tích hợp máy chấm công vật lý thời gian thực.
- Workflow duyệt nhiều cấp.
- Tích hợp kế toán, ERP hoặc bảo hiểm xã hội.
- AI tự động ra quyết định thay con người.

---

## 3. Kiến trúc tổng thể đề xuất

### 3.1. Kiến trúc mức cao

Hệ thống nên được triển khai theo mô hình 3 lớp:

1. Frontend
   - Giao diện web cho nhân viên và quản trị viên.
   - Có thể dùng React, Vue hoặc framework tương đương.
   - Tách rõ khu vực Employee Portal và Admin Portal.

2. Backend API
   - Cung cấp REST API hoặc REST kết hợp WebSocket.
   - Chịu trách nhiệm xác thực, phân quyền, xử lý nghiệp vụ, giao tiếp database, gọi dịch vụ AI.
   - Có thể dùng FastAPI, NestJS, Django hoặc framework backend tương đương.

3. Database và hạ tầng dữ liệu
   - PostgreSQL là lựa chọn khuyến nghị vì phù hợp với quan hệ dữ liệu chặt và nhiều truy vấn đối chiếu.
   - Redis có thể dùng cho cache, queue, session và job nền nếu cần.

### 3.2. Kiến trúc dịch vụ logic

Backend nên được chia thành các module chức năng:

- `auth`: đăng nhập, JWT/session, đổi mật khẩu, khóa tài khoản.
- `users`: thông tin tài khoản và hồ sơ nhân sự.
- `scheduling`: mẫu ca, đăng ký ca, phân công lịch, publish lịch.
- `leave`: tạo đơn nghỉ, duyệt đơn, đồng bộ với lịch.
- `attendance`: lưu check-in/check-out, tính giờ công thực tế.
- `payroll`: kỳ lương, công thức tính, publish phiếu lương.
- `tickets`: khiếu nại lịch và lương.
- `audit`: ghi log thay đổi dữ liệu quan trọng.
- `notifications`: thông báo trong hệ thống, email nếu cần.
- `ai_analytics`: chuẩn hóa dữ liệu, gọi API AI, lưu kết quả phân tích.

### 3.3. Luồng dữ liệu tổng quát

1. Người dùng thao tác trên frontend.
2. Frontend gọi backend API.
3. Backend kiểm tra xác thực, phân quyền và ràng buộc nghiệp vụ.
4. Dữ liệu được đọc/ghi vào PostgreSQL.
5. Với các chức năng AI, backend trích xuất dữ liệu cần thiết, ẩn bớt thông tin nhạy cảm, chuyển thành JSON chuẩn hóa rồi mới gọi API mô hình AI.
6. Kết quả AI được backend kiểm tra định dạng, lưu bản ghi và trả về dashboard quản trị.

### 3.4. Nguyên tắc thiết kế quan trọng

- Mọi thao tác thay đổi dữ liệu quan trọng phải có audit log.
- Không xóa cứng dữ liệu nghiệp vụ trừ khi là dữ liệu test.
- Mọi dữ liệu dùng cho AI phải được lọc thông tin nhạy cảm trước khi gửi ra ngoài.
- Các quyết định nghiệp vụ cuối cùng như duyệt nghỉ, chốt lịch, chốt lương vẫn do Admin xác nhận.

---

## 4. Phân quyền hệ thống (RBAC)

### 4.1. Vai trò

Hiện tại chỉ sử dụng 2 vai trò chính để giảm độ phức tạp:

- `EMPLOYEE`
- `ADMIN`

### 4.2. Ma trận quyền

| Chức năng | Employee | Admin |
|---|---|---|
| Đăng nhập, đổi mật khẩu | Có | Có |
| Xem, sửa hồ sơ cá nhân | Có | Có |
| Xem hồ sơ người khác | Không | Có |
| Tạo tài khoản nhân viên | Không | Có |
| Đăng ký ca làm tuần sau | Có | Có thể thay mặt nhân viên nếu cần |
| Chốt và publish lịch | Không | Có |
| Tạo đơn nghỉ phép | Có | Có thể tạo hộ trong tình huống đặc biệt |
| Duyệt hoặc từ chối nghỉ phép | Không | Có |
| Xem dữ liệu chấm công cá nhân | Có | Có |
| Chỉnh sửa dữ liệu chấm công | Không | Có, phải ghi log |
| Xem phiếu lương cá nhân | Có | Có |
| Chạy tính lương và publish lương | Không | Có |
| Tạo khiếu nại | Có | Có thể tạo hộ |
| Xử lý khiếu nại | Không | Có |
| Xem dashboard AI | Không | Có |
| Xem audit log | Không | Có |

### 4.3. Nguyên tắc phân quyền

- Backend phải là nơi kiểm tra quyền cuối cùng, không chỉ dựa vào việc ẩn nút ở frontend.
- Mỗi API nhạy cảm phải kiểm tra cả `role` lẫn quyền sở hữu dữ liệu.
- Nhân viên chỉ được xem dữ liệu thuộc về chính họ, kể cả phiếu lương, ticket và lịch sử nghỉ phép.

---

## 5. Mô hình dữ liệu cốt lõi

Do giới hạn hệ quản trị hiện tại chỉ cho phép tối đa `5 bảng`, thiết kế database phải chuyển từ mô hình chuẩn hóa cao sang mô hình gộp bảng có kiểm soát. Cách làm này vẫn khả thi nếu thống nhất các nguyên tắc sau:

- Dùng cột `record_type` để phân biệt nhiều loại bản ghi trong cùng một bảng.
- Dùng cột `parent_id` để tạo quan hệ cha con thay cho việc tách nhiều bảng riêng.
- Dùng các cột JSON như `meta_json`, `snapshot_json`, `output_json` cho dữ liệu biến thiên theo loại bản ghi.
- Giữ logic nghiệp vụ ở backend thật chặt vì database lúc này không còn tách thực thể đẹp như thiết kế chuẩn.

### 5.1. Chiến lược gộp bảng

Thiết kế vật lý cuối cùng chỉ còn 5 bảng:

1. `users`
2. `work_records`
3. `leave_requests`
4. `payroll_records`
5. `system_records`

Mapping từ mô hình cũ sang mô hình mới:

- `users` + `employee_profiles` -> `users`
- `shift_templates` + `schedule_periods` + `schedule_slots` + `shift_registrations` + `schedule_assignments` + `attendance_logs` -> `work_records`
- `leave_requests` giữ nguyên thành `leave_requests`
- `payroll_periods` + `payroll_items` -> `payroll_records`
- `grievance_tickets` + `audit_logs` + `ai_reports` -> `system_records`

### 5.2. Bảng `users`

Gộp thông tin tài khoản và hồ sơ nhân viên vào cùng một bảng.

Các trường khuyến nghị:

- `id`
- `email`
- `password_hash`
- `role`
- `status` (`ACTIVE`, `INACTIVE`, `LOCKED`)
- `employee_code`
- `full_name`
- `phone`
- `date_of_birth`
- `gender`
- `department`
- `position`
- `employment_type` (`FULL_TIME`, `PART_TIME`, `CONTRACT`)
- `base_salary`
- `hourly_rate`
- `start_date`
- `last_login_at`
- `created_at`
- `updated_at`
- `deleted_at`

Lý do gộp:

- Tránh phải tách `users` và `employee_profiles`.
- Phù hợp với hệ thống chỉ có 2 vai trò chính và không có yêu cầu hồ sơ quá phức tạp ở giai đoạn đầu.

### 5.3. Bảng `work_records`

Đây là bảng trung tâm, gộp toàn bộ dữ liệu liên quan đến ca làm, đăng ký ca, phân công ca và chấm công.

Các trường khuyến nghị:

- `id`
- `parent_id`
- `record_type`
- `employee_id`
- `week_start_date`
- `week_end_date`
- `work_date`
- `shift_name`
- `start_time`
- `end_time`
- `break_minutes`
- `required_headcount`
- `preference_level`
- `record_status`
- `check_in_at`
- `check_out_at`
- `worked_minutes`
- `late_minutes`
- `overtime_minutes`
- `is_night_shift`
- `source`
- `note`
- `meta_json`
- `created_by`
- `created_at`
- `updated_at`

Giá trị khuyến nghị cho `record_type`:

- `SCHEDULE_PERIOD`: đại diện cho một kỳ lịch theo tuần.
- `SHIFT_TEMPLATE`: mẫu ca chuẩn.
- `SHIFT_SLOT`: một ca cụ thể trong một ngày cụ thể.
- `REGISTRATION`: nguyện vọng đăng ký ca của nhân viên.
- `ASSIGNMENT`: kết quả phân công ca chính thức.
- `ATTENDANCE`: dữ liệu check-in/check-out thực tế.

Quy ước dùng `parent_id`:

- `SHIFT_SLOT.parent_id` trỏ tới bản ghi `SCHEDULE_PERIOD`.
- `REGISTRATION.parent_id` trỏ tới bản ghi `SHIFT_SLOT`.
- `ASSIGNMENT.parent_id` trỏ tới bản ghi `SHIFT_SLOT`.
- `ATTENDANCE.parent_id` trỏ tới bản ghi `ASSIGNMENT`.

Quy ước bổ sung:

- Nếu cần biết slot được sinh từ mẫu ca nào, lưu `template_id` trong `meta_json` của `SHIFT_SLOT`.
- Trạng thái tuần như mở đăng ký, đang review hay đã publish được lưu ở bản ghi `SCHEDULE_PERIOD`.
- Với `SCHEDULE_PERIOD`, nên lưu thêm `registration_open_at`, `registration_close_at`, `published_at` trong `meta_json`.
- Với `ASSIGNMENT`, nên lưu thêm `assigned_at`, `removed_at`, `removal_reason` trong `meta_json`.

Ví dụ về `record_status` theo từng loại:

- `SCHEDULE_PERIOD`: `DRAFT`, `REGISTRATION_OPEN`, `REVIEWING`, `PUBLISHED`, `LOCKED`
- `SHIFT_SLOT`: `OPEN`, `FILLED`, `UNDERSTAFFED`, `CANCELLED`
- `REGISTRATION`: `PENDING`, `ACCEPTED`, `REJECTED`, `WITHDRAWN`
- `ASSIGNMENT`: `ASSIGNED`, `REMOVED`
- `ATTENDANCE`: `PRESENT`, `LATE`, `ABSENT`, `HALF_DAY`, `ON_LEAVE`

Lý do gộp:

- Đây là nhóm bảng nhiều nhất trong thiết kế cũ.
- Dùng một bảng dạng event/polymorphic sẽ giúp vẫn giữ được đầy đủ nghiệp vụ dù database bị giới hạn bảng.

### 5.4. Bảng `leave_requests`

Giữ riêng bảng nghỉ phép vì đây là thực thể có vòng đời trạng thái rõ ràng, tác động trực tiếp tới lịch làm, chấm công và tính lương.

Các trường khuyến nghị:

- `id`
- `employee_id`
- `leave_type` (`ANNUAL`, `SICK`, `UNPAID`, `OTHER`)
- `start_date`
- `end_date`
- `reason`
- `status` (`PENDING`, `APPROVED`, `REJECTED`, `CANCELLED`)
- `reviewed_by`
- `reviewed_at`
- `rejection_reason`
- `created_at`
- `updated_at`

Lý do chưa gộp bảng này:

- Đơn nghỉ có logic duyệt riêng và thường được query độc lập.
- Nếu ép nhập vào `work_records`, câu truy vấn kiểm tra xung đột ngày nghỉ sẽ khó đọc và khó bảo trì hơn nhiều.

### 5.5. Bảng `payroll_records`

Gộp kỳ lương và phiếu lương chi tiết của từng nhân viên vào một bảng.

Các trường khuyến nghị:

- `id`
- `parent_id`
- `record_type`
- `employee_id`
- `name`
- `period_start`
- `period_end`
- `status`
- `base_amount`
- `overtime_amount`
- `allowance_amount`
- `deduction_amount`
- `final_amount`
- `calculation_snapshot_json`
- `published_at`
- `created_by`
- `created_at`
- `updated_at`

Giá trị khuyến nghị cho `record_type`:

- `PERIOD`: đại diện cho kỳ lương tổng.
- `ITEM`: đại diện cho phiếu lương của một nhân viên.

Quy ước dùng `parent_id`:

- Bản ghi `ITEM.parent_id` trỏ tới bản ghi `PERIOD`.

Quy ước dùng `status`:

- `PERIOD`: `DRAFT`, `CALCULATED`, `REVIEWING`, `PUBLISHED`, `LOCKED`
- `ITEM`: `DRAFT`, `READY`, `PUBLISHED`, `ADJUSTED`

Lý do gộp:

- Vẫn giữ được kỳ lương và phiếu lương theo cấu trúc cha con mà không cần 2 bảng riêng.

### 5.6. Bảng `system_records`

Gộp ticket khiếu nại, audit log và báo cáo AI vào một bảng hệ thống tổng hợp.

Các trường khuyến nghị:

- `id`
- `record_type`
- `employee_id`
- `actor_user_id`
- `related_entity_type`
- `related_entity_id`
- `status`
- `title`
- `description`
- `action`
- `input_snapshot_json`
- `output_json`
- `old_value_json`
- `new_value_json`
- `model_name`
- `handled_by`
- `handled_at`
- `created_at`
- `updated_at`

Giá trị khuyến nghị cho `record_type`:

- `TICKET`
- `AUDIT`
- `AI_REPORT`

Quy ước sử dụng:

- Nếu `record_type = TICKET`, dùng `title`, `description`, `status`, `handled_by`, `handled_at`.
- Nếu `record_type = AUDIT`, dùng `action`, `old_value_json`, `new_value_json`, `actor_user_id`.
- Nếu `record_type = AI_REPORT`, dùng `input_snapshot_json`, `output_json`, `model_name`.

Lý do gộp:

- Cả 3 nhóm dữ liệu này đều có tính chất lưu vết, không phải transaction cốt lõi theo thời gian thực.
- Việc gộp giúp tiết kiệm bảng mà vẫn dễ mở rộng theo `record_type`.

### 5.7. Chỉ mục bắt buộc khi dùng mô hình gộp

Vì số bảng ít nhưng dữ liệu dồn nhiều vào cùng bảng, cần tạo index cẩn thận:

- `users(email)`
- `users(employee_code)`
- `work_records(record_type, work_date)`
- `work_records(record_type, employee_id, work_date)`
- `work_records(parent_id)`
- `leave_requests(employee_id, start_date, end_date, status)`
- `payroll_records(record_type, period_start, period_end)`
- `payroll_records(parent_id)`
- `system_records(record_type, related_entity_type, related_entity_id)`

### 5.8. Chiến lược xóa dữ liệu

- `users` và `work_records` có thể dùng soft delete nếu cần.
- Không xóa cứng `leave_requests`, `payroll_records` và `system_records` vì đây là dữ liệu đối chiếu lịch sử.
- Các bản ghi vô hiệu hóa phải được ẩn khỏi luồng sử dụng thường ngày nhưng vẫn truy lại được cho admin.

---

## 6. Quy tắc nghiệp vụ tổng quát

Các quy tắc này áp dụng xuyên suốt hệ thống:

1. Một nhân viên không được có 2 ca trùng thời gian trong cùng một ngày.
2. Một nhân viên đã có đơn nghỉ phép ở trạng thái `PENDING` hoặc `APPROVED` thì không được đăng ký hoặc bị phân vào ca thuộc ngày nghỉ đó.
3. Một kỳ lương không được publish nếu còn ticket liên quan tới kỳ đó đang ở trạng thái `OPEN` hoặc `IN_PROGRESS`.
4. Mọi chỉnh sửa tác động đến giờ công, lương, duyệt nghỉ hoặc phân công ca phải ghi audit log.
5. Các thao tác quan trọng phải dùng transaction database để tránh dữ liệu lệch trạng thái.

---

## 7. Đặc tả nghiệp vụ chi tiết theo phân hệ

### 7.1. Phân hệ xác thực và quản lý hồ sơ

#### Mục tiêu

Cho phép người dùng đăng nhập an toàn và quản lý đúng dữ liệu của mình.

#### Chức năng chính

- Đăng nhập bằng email và mật khẩu.
- Đổi mật khẩu.
- Quên mật khẩu qua email nếu hệ thống có cấu hình SMTP.
- Admin tạo và khóa tài khoản.
- Nhân viên cập nhật một số trường hồ sơ cá nhân được cho phép.

#### Ràng buộc

- Mật khẩu phải được băm bằng thuật toán an toàn như bcrypt hoặc Argon2.
- Không lưu mật khẩu thô.
- Tài khoản bị khóa không thể đăng nhập.
- Nếu thay đổi email hoặc trạng thái tài khoản, hệ thống phải ghi audit log.

#### Tiêu chí hoàn thành

- Người dùng đăng nhập đúng vai trò.
- Nhân viên không thể truy cập API hồ sơ của người khác.
- Admin có thể khóa tài khoản mà không xóa lịch sử dữ liệu.

### 7.2. Phân hệ quản lý lịch làm việc

#### Mục tiêu

Quản lý toàn bộ quy trình mở đăng ký ca, thu nguyện vọng, chốt lịch và publish lịch cho tuần kế tiếp.

#### Luồng nghiệp vụ chuẩn

1. Admin tạo một bản ghi `work_records` loại `SCHEDULE_PERIOD` cho tuần mới.
2. Admin sinh các bản ghi `work_records` loại `SHIFT_SLOT` dựa trên mẫu ca `SHIFT_TEMPLATE` và gắn `parent_id` về `SCHEDULE_PERIOD`.
3. Admin mở cổng đăng ký trong khoảng thời gian xác định.
4. Nhân viên vào xem các ca còn mở và đăng ký nguyện vọng.
5. Sau thời điểm đóng đăng ký, Admin vào màn hình review để phân ca.
6. Hệ thống kiểm tra ràng buộc về nghỉ phép, trùng ca, giới hạn giờ làm.
7. Admin publish lịch chính thức.
8. Sau khi publish, nhân viên chỉ được xem lịch, không còn quyền tự chỉnh.

#### Trạng thái chính

```text
Schedule Period:
DRAFT -> REGISTRATION_OPEN -> REVIEWING -> PUBLISHED -> LOCKED
```

#### Ràng buộc bắt buộc

- Không cho phép đăng ký ca ngoài thời gian mở cổng.
- Không cho phép đăng ký ca thuộc ngày đã có đơn nghỉ phép `PENDING` hoặc `APPROVED`.
- Không cho phép một nhân viên có 2 bản ghi `work_records` loại `ASSIGNMENT` trùng thời gian.
- Không cho phép vượt giới hạn giờ làm theo cấu hình hệ thống, ví dụ `48 giờ/tuần`.
- Nếu một ca chưa đủ người, trạng thái slot phải là `UNDERSTAFFED`.

#### Hành vi giao diện khuyến nghị

- Employee thấy lịch theo dạng tuần và trạng thái từng ca.
- Admin có màn hình kéo thả hoặc chọn nhanh để phân ca.
- Ca bị thiếu người được tô nổi bật để dễ xử lý.

#### Tính khả thi triển khai

Để đơn giản hóa giai đoạn đầu:

- Không cần tối ưu xếp lịch hoàn toàn tự động.
- Cho phép Admin chốt thủ công, AI chỉ đóng vai trò gợi ý.
- Có thể dùng batch validation khi nhấn nút publish thay vì kiểm tra thời gian thực quá phức tạp.

### 7.3. Phân hệ nghỉ phép

#### Mục tiêu

Quản lý đơn xin nghỉ phép có luồng duyệt rõ ràng và đồng bộ với lịch làm.

#### Luồng nghiệp vụ chuẩn

1. Nhân viên tạo đơn nghỉ phép.
2. Hệ thống kiểm tra dữ liệu đầu vào.
3. Đơn mới ở trạng thái `PENDING`.
4. Admin xem danh sách đơn chờ duyệt.
5. Admin chọn `APPROVED` hoặc `REJECTED`.
6. Nếu đơn được duyệt, hệ thống tự cập nhật tác động tới lịch và chấm công liên quan.
7. Nếu nhân viên muốn hủy đơn, chỉ được hủy khi đơn còn `PENDING`.

#### Trạng thái chính

```text
Leave Request:
PENDING -> APPROVED
PENDING -> REJECTED
PENDING -> CANCELLED
```

#### Ràng buộc bắt buộc

- `start_date` không được sau `end_date`.
- Không tạo đơn trùng khoảng thời gian với đơn đang `PENDING` hoặc `APPROVED`.
- Nhân viên chỉ được sửa hoặc hủy khi trạng thái là `PENDING`.
- Nếu Admin từ chối, `rejection_reason` là bắt buộc.
- Nếu Admin duyệt, mọi bản ghi `work_records` loại `ASSIGNMENT` trùng ngày nghỉ phải được cập nhật trong cùng transaction.

#### Hành vi hệ thống khi duyệt nghỉ

Khi đơn chuyển sang `APPROVED`, hệ thống cần:

1. Tìm các ca đã phân công cho nhân viên trong khoảng ngày nghỉ.
2. Chuyển `record_status` của bản ghi `ASSIGNMENT` thành `REMOVED`.
3. Ghi `removal_reason = "AUTO_REMOVED_DUE_TO_APPROVED_LEAVE"`.
4. Đánh dấu bản ghi `SHIFT_SLOT` cha liên quan là `UNDERSTAFFED` nếu thiếu người.
5. Tạo audit log cho toàn bộ chuỗi thao tác.

### 7.4. Phân hệ chấm công

#### Mục tiêu

Tạo dữ liệu giờ công đáng tin cậy để làm cơ sở tính lương và xử lý khiếu nại.

#### Chức năng chính

- Nhập hoặc đồng bộ dữ liệu check-in/check-out.
- Tính số phút làm việc thực tế.
- Tính số phút đi muộn và tăng ca.
- Hiển thị bảng công cá nhân.
- Cho phép Admin điều chỉnh dữ liệu trong trường hợp ngoại lệ.

#### Ràng buộc bắt buộc

- Một ngày một nhân viên chỉ nên có một bản ghi chấm công tổng hợp cho mỗi ca hoặc một cấu trúc đủ rõ để hợp nhất.
- Nếu có đơn nghỉ `APPROVED`, trạng thái chấm công ngày đó nên là `ON_LEAVE`.
- Nếu Admin sửa giờ công thủ công, phải ghi lý do và audit log.

#### Công thức tính cơ bản

- `worked_minutes = check_out_at - check_in_at - break_minutes`
- `late_minutes = max(0, actual_check_in - scheduled_start_time)`
- `overtime_minutes = max(0, actual_check_out - scheduled_end_time)`

#### Lưu ý triển khai

Giai đoạn đầu có thể cho phép Admin nhập dữ liệu chấm công thủ công hoặc import file CSV trước khi tích hợp máy chấm công thực tế.

### 7.5. Phân hệ khiếu nại

#### Mục tiêu

Cho phép nhân viên báo cáo sai sót liên quan tới lịch, công hoặc lương và tạo quy trình xử lý có lưu vết.

#### Luồng nghiệp vụ chuẩn

1. Nhân viên tạo ticket từ màn hình phiếu lương, bảng công hoặc lịch làm.
2. Ticket vào trạng thái `OPEN`.
3. Admin nhận ticket và chuyển sang `IN_PROGRESS`.
4. Admin đối chiếu dữ liệu liên quan.
5. Admin chỉnh dữ liệu nếu cần.
6. Admin ghi chú kết quả xử lý.
7. Ticket chuyển sang `RESOLVED` hoặc `REJECTED`.

#### Trạng thái chính

```text
Ticket:
OPEN -> IN_PROGRESS -> RESOLVED
OPEN -> IN_PROGRESS -> REJECTED
RESOLVED -> CLOSED
```

#### Ràng buộc bắt buộc

- Ticket lương chỉ được tạo trong vòng `48 giờ` kể từ khi phiếu lương được publish.
- Nhân viên chỉ được xem ticket của chính họ.
- Mọi thay đổi dữ liệu phát sinh từ quá trình xử lý ticket đều phải có audit log.
- Một ticket đã `RESOLVED` hoặc `REJECTED` không được chỉnh sửa nội dung gốc, chỉ được thêm phản hồi hoặc đóng.

#### Yêu cầu giao diện cho Admin

Màn hình xử lý ticket nên hiển thị cùng lúc:

- Nội dung khiếu nại.
- Dữ liệu nguồn liên quan như lịch phân ca, chấm công, phiếu lương.
- Lịch sử các thao tác đã thực hiện.

### 7.6. Phân hệ tính lương

#### Mục tiêu

Tự động hóa việc tổng hợp giờ công, phụ cấp, khấu trừ để sinh phiếu lương theo kỳ.

#### Luồng nghiệp vụ chuẩn

1. Admin tạo kỳ lương.
2. Hệ thống lấy dữ liệu chấm công trong khoảng thời gian kỳ lương.
3. Hệ thống tính lương từng nhân viên theo công thức cấu hình.
4. Kết quả ở trạng thái `CALCULATED`.
5. Admin kiểm tra và điều chỉnh nếu cần.
6. Khi không còn ticket đang mở liên quan, Admin publish phiếu lương.
7. Sau khi publish, nhân viên có thể xem phiếu lương.

#### Trạng thái chính

```text
Payroll Period:
DRAFT -> CALCULATED -> REVIEWING -> PUBLISHED -> LOCKED
```

#### Công thức tối thiểu nên hỗ trợ

`final_amount = base_amount + overtime_amount + allowance_amount - deduction_amount`

Trong đó:

- `base_amount` có thể lấy theo lương tháng quy đổi hoặc số giờ công chuẩn.
- `overtime_amount` dựa trên số phút tăng ca.
- `allowance_amount` là phụ cấp do Admin nhập hoặc rule hệ thống.
- `deduction_amount` là khấu trừ đi muộn, vắng không phép hoặc các khoản khác.

#### Ràng buộc bắt buộc

- Không publish lương nếu còn ticket `OPEN` hoặc `IN_PROGRESS` liên quan đến kỳ đó.
- Khi tính lương xong phải lưu `calculation_snapshot_json` để về sau đối chiếu lại được công thức tại thời điểm publish.
- Nếu Admin chỉnh tay số tiền lương, hệ thống phải bắt buộc nhập lý do.

#### Tính khả thi triển khai

Giai đoạn đầu chỉ nên hỗ trợ một công thức lương thống nhất hoặc một số cấu hình đơn giản. Tránh làm quá nhiều loại hợp đồng phức tạp ngay từ đầu.

---

## 8. Tính năng AI và cách tích hợp khả thi

Mục tiêu của AI là hỗ trợ phân tích và gợi ý. AI không trực tiếp ghi đè lịch, nghỉ phép hay lương.

### 8.1. Nguyên tắc triển khai AI

- Chỉ Admin mới được dùng AI Dashboard.
- Dữ liệu gửi ra ngoài phải bỏ thông tin nhạy cảm không cần thiết như số điện thoại, email, địa chỉ.
- Mỗi lần gọi AI phải lưu input snapshot và output để kiểm tra sau này.
- Backend phải kiểm tra định dạng output trước khi hiển thị.
- Nếu AI lỗi hoặc timeout, hệ thống vẫn phải hoạt động bình thường với luồng thủ công.

### 8.2. Module 1: Burnout Prediction

#### Mục tiêu

Phát hiện sớm nguy cơ kiệt sức của nhân viên dựa trên dữ liệu 30 ngày gần nhất.

#### Dữ liệu đầu vào

- Tổng giờ làm theo tuần.
- Số ca đêm.
- Tần suất tăng ca.
- Số ngày nghỉ ốm.
- Tần suất đi muộn hoặc vắng.

#### Kết quả mong muốn

- Danh sách nhân viên có mức rủi ro `LOW`, `MEDIUM`, `HIGH`.
- Lý do giải thích ngắn gọn.
- Gợi ý điều chỉnh như giảm ca đêm, thêm ngày nghỉ, đổi phân bổ ca.

#### Lưu ý thực tế

Đây là tính năng gợi ý quản trị, không được dùng như công cụ đánh giá kỷ luật tự động.

### 8.3. Module 2: Smart Scheduler

#### Mục tiêu

Hỗ trợ Admin tạo một bản nháp xếp lịch tối ưu hơn dựa trên đăng ký và ràng buộc vận hành.

#### Dữ liệu đầu vào

- Danh sách ca cần người.
- Số lượng người cần cho từng ca.
- Danh sách nhân viên sẵn sàng làm việc.
- Đơn nghỉ phép.
- Giới hạn số giờ làm mỗi tuần.
- Ưu tiên cân bằng ca đêm, ca cuối tuần và nhân sự mới/cũ.

#### Kết quả mong muốn

- Một đề xuất phân công ca dưới dạng JSON.
- Danh sách cảnh báo nếu có ca chưa đủ người.
- Giải thích vì sao AI chọn một số phân công nhất định.

#### Cách triển khai khả thi

- AI chỉ sinh bản đề xuất.
- Backend vẫn phải chạy lại bộ validate nội bộ trước khi cho Admin áp dụng.
- Admin phải bấm xác nhận trước khi lịch được lưu chính thức.

### 8.4. Module 3: Generative Weekly Report

#### Mục tiêu

Tự động tạo báo cáo tóm tắt vận hành tuần cho quản trị viên.

#### Nội dung báo cáo nên có

- Biến động tổng giờ làm.
- Tỷ lệ thiếu ca.
- Tỷ lệ đi muộn hoặc nghỉ phép.
- Chi phí lương tăng hoặc giảm bất thường.
- Danh sách cảnh báo đáng chú ý.
- Gợi ý vận hành cho tuần tiếp theo.

#### Đầu ra mong muốn

- Một đoạn báo cáo tự nhiên bằng tiếng Việt.
- Có thể kèm một object JSON chứa số liệu chính để frontend dễ hiển thị.

---

## 9. Bảo mật, quyền riêng tư và audit

### 9.1. Bảo mật bắt buộc

- Mật khẩu phải được băm an toàn.
- Token đăng nhập phải có thời hạn.
- API nhạy cảm phải kiểm tra vai trò và quyền sở hữu dữ liệu.
- Dữ liệu truyền qua mạng phải dùng HTTPS khi triển khai thực tế.

### 9.2. Quyền riêng tư dữ liệu

- Chỉ thu thập dữ liệu cần thiết cho nghiệp vụ.
- Dữ liệu gửi qua AI phải được tối giản.
- Không gửi thông tin định danh không cần thiết sang bên thứ ba.

### 9.3. Audit log bắt buộc cho các hành động sau

- Tạo, sửa, khóa tài khoản.
- Duyệt hoặc từ chối nghỉ phép.
- Chỉnh sửa chấm công.
- Phân công hoặc gỡ ca.
- Chạy tính lương, chỉnh lương, publish lương.
- Xử lý ticket.

---

## 10. Yêu cầu phi chức năng

### 10.1. Hiệu năng

- Danh sách lịch, ticket, bảng công và phiếu lương phải có phân trang.
- Dashboard AI có thể xử lý bất đồng bộ nếu thời gian phản hồi lâu.

### 10.2. Tính nhất quán dữ liệu

- Các thao tác nghiệp vụ liên thông phải dùng transaction.
- Không để trạng thái nghỉ phép, lịch và lương mâu thuẫn nhau.

### 10.3. Khả năng mở rộng

- Thiết kế bảng và API theo module để dễ thêm vai trò hoặc tích hợp chấm công sau này.
- Tách service AI để thay đổi nhà cung cấp mô hình dễ dàng.

### 10.4. Khả năng vận hành

- Có seed dữ liệu demo để test.
- Có log lỗi backend.
- Có cơ chế backup database định kỳ khi đưa lên production.

### 10.5. Timezone và định dạng thời gian

- Toàn hệ thống phải thống nhất một timezone, ví dụ `Asia/Ho_Chi_Minh`.
- Các phép tính giờ công phải dựa trên timezone thống nhất để tránh lệch ngày.

---

## 11. API và luồng xử lý khuyến nghị

Phần này không bắt buộc liệt kê toàn bộ endpoint, nhưng nên bám theo nhóm API sau:

- `POST /auth/login`
- `GET /me`
- `PATCH /me/profile`
- `GET /employees`
- `POST /schedule-periods`
- `POST /schedule-periods/{id}/open-registration`
- `POST /schedule-slots/{id}/register`
- `POST /schedule-periods/{id}/publish`
- `POST /leave-requests`
- `PATCH /leave-requests/{id}/approve`
- `PATCH /leave-requests/{id}/reject`
- `GET /attendance`
- `PATCH /attendance/{id}`
- `POST /payroll-periods`
- `POST /payroll-periods/{id}/calculate`
- `POST /payroll-periods/{id}/publish`
- `POST /tickets`
- `PATCH /tickets/{id}/status`
- `POST /ai/reports/burnout`
- `POST /ai/reports/scheduler`
- `POST /ai/reports/weekly-summary`

Khi triển khai thật, mỗi endpoint cần có:

- Kiểm tra quyền.
- Validate dữ liệu đầu vào.
- Bắt lỗi nghiệp vụ rõ ràng.
- Ghi audit log nếu là hành động quan trọng.

---

## 12. Lộ trình triển khai từ đầu đến cuối

Phần này là trình tự khuyến nghị để đội phát triển follow xuyên suốt.

### Giai đoạn 1: Khởi tạo dự án và nền tảng

Mục tiêu:

- Tạo cấu trúc frontend, backend, database.
- Cấu hình môi trường phát triển.
- Thiết lập migration database và seed dữ liệu.

Deliverables:

- Repository chuẩn.
- Kết nối PostgreSQL.
- Mô hình bảng cơ bản.
- Cơ chế đăng nhập thử nghiệm.

### Giai đoạn 2: Xác thực, người dùng và phân quyền

Mục tiêu:

- Hoàn thiện login, vai trò, hồ sơ nhân viên.

Deliverables:

- API auth.
- API hồ sơ người dùng.
- Guard kiểm tra vai trò.
- Giao diện đăng nhập và trang profile.

### Giai đoạn 3: Quản lý lịch làm và nghỉ phép

Mục tiêu:

- Hoàn thiện luồng đăng ký ca, duyệt và publish lịch.
- Hoàn thiện đơn nghỉ phép và đồng bộ với lịch.

Deliverables:

- Các bảng lịch và nghỉ phép.
- Validation trùng ca, giới hạn giờ và xung đột nghỉ phép.
- Giao diện employee đăng ký ca.
- Giao diện admin chốt lịch và duyệt đơn.

### Giai đoạn 4: Chấm công và tính lương

Mục tiêu:

- Tạo nền tảng để tính lương từ dữ liệu công.

Deliverables:

- API và giao diện chấm công.
- API tính lương theo kỳ.
- Phiếu lương cá nhân.
- Rule khóa publish nếu còn ticket mở.

### Giai đoạn 5: Khiếu nại và audit log

Mục tiêu:

- Bổ sung khả năng đối chiếu và xử lý sai sót.

Deliverables:

- Ticket system đầy đủ trạng thái.
- Audit log cho các thao tác quan trọng.
- Màn hình admin xử lý ticket kèm dữ liệu liên quan.

### Giai đoạn 6: AI analytics

Mục tiêu:

- Tích hợp AI theo hướng an toàn, có kiểm soát.

Deliverables:

- Service chuẩn hóa dữ liệu đầu vào.
- Gọi API mô hình AI.
- Dashboard hiển thị 3 loại kết quả AI.
- Lưu input/output để audit.

### Giai đoạn 7: Kiểm thử, tối ưu và triển khai

Mục tiêu:

- Hoàn thiện chất lượng và sẵn sàng đưa vào môi trường thực tế.

Deliverables:

- Test nghiệp vụ chính.
- Kiểm tra phân quyền.
- Kiểm tra hiệu năng cơ bản.
- Tài liệu cài đặt, tài liệu vận hành và dữ liệu demo.

---

## 13. Bộ kiểm thử nghiệp vụ tối thiểu

Hệ thống nên có ít nhất các test case sau:

1. Nhân viên không thể xem phiếu lương của người khác.
2. Nhân viên không thể đăng ký 2 ca trùng nhau.
3. Nhân viên có đơn nghỉ `APPROVED` không thể được phân ca trùng ngày nghỉ.
4. Admin từ chối nghỉ phép nhưng không nhập lý do thì không lưu được.
5. Khi duyệt nghỉ phép, ca đã phân công của nhân viên được gỡ đúng và ghi log.
6. Không publish lương nếu còn ticket đang `OPEN` hoặc `IN_PROGRESS`.
7. Khi Admin sửa dữ liệu chấm công hoặc lương, audit log được tạo.
8. Nếu AI trả output sai format, backend không làm hỏng dashboard và trả lỗi có kiểm soát.

---

## 14. Rủi ro triển khai và hướng xử lý

### Rủi ro 0: Mô hình 5 bảng làm câu truy vấn và validation phức tạp hơn

Hướng xử lý:

- Chuẩn hóa chặt enum `record_type` và `record_status`.
- Tạo service/backend layer riêng cho từng nghiệp vụ thay vì query trực tiếp tùy hứng.
- Bắt buộc dùng index cho các cột lọc chính như `record_type`, `employee_id`, `work_date`, `parent_id`.
- Dùng `meta_json` có quy ước rõ ràng, không lưu dữ liệu tùy tiện.

### Rủi ro 1: Luồng nghiệp vụ chồng chéo giữa lịch, nghỉ, công và lương

Hướng xử lý:

- Thiết kế state machine rõ ràng.
- Dùng transaction ở các điểm chuyển trạng thái quan trọng.
- Viết test tích hợp cho các case giao thoa.

### Rủi ro 2: AI gợi ý thiếu ổn định hoặc không đúng định dạng

Hướng xử lý:

- Dùng schema JSON để validate output.
- Có fallback thủ công.
- Không để AI ghi trực tiếp vào bảng nghiệp vụ chính.

### Rủi ro 3: Dữ liệu production nhạy cảm

Hướng xử lý:

- Tối giản dữ liệu gửi AI.
- Ghi nhận lịch sử gọi AI.
- Giới hạn quyền truy cập dashboard AI cho Admin.

---

## 15. Kết luận

Hệ thống này khả thi nếu triển khai theo hướng module, ưu tiên MVP rõ ràng và giữ nguyên tắc: nghiệp vụ cốt lõi phải chắc trước, AI là lớp hỗ trợ tăng giá trị sau. Trọng tâm của bản đặc tả không phải là làm mọi thứ thật phức tạp ngay từ đầu, mà là tạo ra một thiết kế đủ đầy, nhất quán và có thể phát triển dần mà không phá vỡ nền tảng.

Nếu bám đúng tài liệu này, đội phát triển có thể bắt đầu từ database, backend API, frontend quản trị và quy trình kiểm thử một cách tuần tự, đồng thời vẫn để sẵn không gian mở rộng cho các tính năng AI nâng cao ở các giai đoạn sau.
