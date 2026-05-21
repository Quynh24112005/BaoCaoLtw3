import mysql.connector
from faker import Faker
import random
from datetime import datetime, date, timedelta
import json

# Khoi tao Faker voi tieng Viet
fake = Faker('vi_VN')

db_config = {
    'host': 'localhost',
    'user': 'root',
    'password': '2411',
    'database': 'hr_management',
    'charset': 'utf8mb4'
}

def seed_database():
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor()
        print("Da ket noi toi database hr_management thanh cong!")

        # ---------------------------------------------------------
        # 1. Clear old data (keep original 3 users to avoid lockout)
        # ---------------------------------------------------------
        print("Dang xoa du lieu cu...")
        cursor.execute("SET FOREIGN_KEY_CHECKS = 0")
        cursor.execute("DELETE FROM system_records")
        cursor.execute("DELETE FROM payroll_records")
        cursor.execute("DELETE FROM leave_requests")
        cursor.execute("DELETE FROM work_records")
        cursor.execute("DELETE FROM users WHERE id > 3")
        cursor.execute("SET FOREIGN_KEY_CHECKS = 1")
        conn.commit()

        # ---------------------------------------------------------
        # 2. Seed 15 new employees (users table)
        # ---------------------------------------------------------
        print("Dang tao 15 nhan vien moi...")
        departments = [
            ('Kỹ thuật', ['Lập trình viên', 'Kỹ sư phần mềm', 'QA Tester', 'Tech Lead']),
            ('Kinh doanh', ['Nhân viên Sales', 'Trưởng phòng Kinh doanh', 'Account Manager']),
            ('Nhân sự', ['Nhân viên HR', 'HR Business Partner']),
            ('Tài chính', ['Kế toán viên', 'Kiểm toán viên']),
            ('Marketing', ['Content Creator', 'Designer', 'SEO Specialist'])
        ]

        employee_ids = [2, 3] # Include original employees (id=2, id=3)
        # We will insert new users starting from id=4 to 18
        pass_hash = '$2y$10$/Ix2AQVbuB9gp7xZVZEUMeq1X/jRAFWxbtxgCy/s5SIteXeN0rEbm' # Hash for '123456'

        for i in range(4, 19):
            dept, positions = random.choice(departments)
            pos = random.choice(positions)
            
            email = f"nv{i}@hr.vn"
            emp_code = f"EMP{str(i).zfill(4)}"
            full_name = fake.name()
            phone = fake.phone_number()[:20]
            dob = fake.date_of_birth(minimum_age=20, maximum_age=50)
            gender = random.choice(['MALE', 'FEMALE'])
            emp_type = random.choice(['FULL_TIME', 'PART_TIME', 'CONTRACT'])
            
            if emp_type == 'FULL_TIME':
                base_sal = float(random.randint(10, 30) * 1000000)
                hr_rate = float(round(base_sal / 160, 0))
            else:
                base_sal = 0.0
                hr_rate = float(random.randint(30, 80) * 1000)
                
            start_dt = fake.date_between(start_date='-2y', end_date='-1m')
            
            cursor.execute("""
                INSERT INTO users (
                    id, email, password_hash, role, status, employee_code, 
                    full_name, phone, date_of_birth, gender, department, 
                    position, employment_type, base_salary, hourly_rate, start_date
                ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
            """, (
                i, email, pass_hash, 'EMPLOYEE', 'ACTIVE', emp_code,
                full_name, phone, dob, gender, dept,
                pos, emp_type, base_sal, hr_rate, start_dt
            ))
            employee_ids.append(i)
        
        conn.commit()
        print(f"Da tao 15 nhan vien moi (Tong cong {len(employee_ids)} nhan vien).")

        # ---------------------------------------------------------
        # 3. Seed leave_requests
        # ---------------------------------------------------------
        print("Dang tao don nghi phep...")
        leave_types = ['ANNUAL', 'SICK', 'UNPAID']
        leave_reasons = [
            "Nghỉ ốm đi khám bệnh", "Giải quyết việc cá nhân gia đình",
            "Nghỉ phép thường niên đi du lịch", "Có việc bận đột xuất", "Chăm sóc người thân bị ốm"
        ]
        
        # We will create 15 leave requests in the past and future
        leave_data = []
        # approved/rejected leave requests for April & May 2026
        for emp_id in random.sample(employee_ids, 10):
            start_date_val = fake.date_between(start_date='-30d', end_date='-2d')
            duration = random.randint(1, 3)
            end_date_val = start_date_val + timedelta(days=duration - 1)
            
            l_type = random.choice(leave_types)
            status = random.choice(['APPROVED', 'REJECTED'])
            reason = random.choice(leave_reasons)
            rej_reason = "Không phù hợp lịch dự án" if status == 'REJECTED' else None
            
            cursor.execute("""
                INSERT INTO leave_requests (
                    employee_id, leave_type, start_date, end_date, reason, status, 
                    reviewed_by, reviewed_at, rejection_reason
                ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
            """, (
                emp_id, l_type, start_date_val, end_date_val, reason, status,
                1, datetime.now() - timedelta(days=5), rej_reason
            ))
            
            if status == 'APPROVED':
                leave_data.append((emp_id, start_date_val, end_date_val))

        # 1-2 pending leave requests for the future
        for emp_id in random.sample(employee_ids, 3):
            start_date_val = fake.date_between(start_date='+2d', end_date='+10d')
            duration = random.randint(1, 2)
            end_date_val = start_date_val + timedelta(days=duration - 1)
            l_type = random.choice(leave_types)
            reason = "Nghỉ phép gia đình"
            cursor.execute("""
                INSERT INTO leave_requests (
                    employee_id, leave_type, start_date, end_date, reason, status
                ) VALUES (%s, %s, %s, %s, %s, %s)
            """, (emp_id, l_type, start_date_val, end_date_val, reason, 'PENDING'))

        conn.commit()
        print("Da tao don nghi phep xong.")

        # Helper to check if employee is on approved leave
        def is_on_leave(emp_id, day):
            for l_emp, l_start, l_end in leave_data:
                if l_emp == emp_id and l_start <= day <= l_end:
                    return True
            return False

        # ---------------------------------------------------------
        # 4. Seed work_records (SCHEDULE_PERIOD, SHIFT_SLOT, etc.)
        # ---------------------------------------------------------
        print("Dang tao chu ky lich lam viec va cham cong...")
        # We will create 5 weekly periods
        # Week 1: 2026-04-20 to 2026-04-26 (Past - PUBLISHED)
        # Week 2: 2026-04-27 to 2026-05-03 (Past - PUBLISHED)
        # Week 3: 2026-05-04 to 2026-05-10 (Past - PUBLISHED)
        # Week 4: 2026-05-11 to 2026-05-17 (Past - PUBLISHED)
        # Week 5: 2026-05-18 to 2026-05-24 (Current - PUBLISHED / IN PROGRESS)
        # Week 6: 2026-05-25 to 2026-05-31 (Future - REGISTRATION_OPEN)
        
        weeks = [
            (date(2026, 4, 20), date(2026, 4, 26), 'PUBLISHED'),
            (date(2026, 4, 27), date(2026, 5, 3), 'PUBLISHED'),
            (date(2026, 5, 4), date(2026, 5, 10), 'PUBLISHED'),
            (date(2026, 5, 11), date(2026, 5, 17), 'PUBLISHED'),
            (date(2026, 5, 18), date(2026, 5, 24), 'PUBLISHED'),
            (date(2026, 5, 25), date(2026, 5, 31), 'REGISTRATION_OPEN')
        ]
        
        shifts_def = [
            ('Ca Sáng', '06:00:00', '14:00:00'),
            ('Ca Chiều', '14:00:00', '22:00:00'),
            ('Ca Đêm', '22:00:00', '06:00:00')
        ]
        
        for w_start, w_end, w_status in weeks:
            # Create Period
            meta_json = {
                'registration_open_at': (w_start - timedelta(days=7)).strftime('%Y-%m-%d 08:00:00'),
                'registration_close_at': (w_start - timedelta(days=2)).strftime('%Y-%m-%d 18:00:00'),
                'published_at': (w_start - timedelta(days=1)).strftime('%Y-%m-%d 09:00:00')
            }
            
            cursor.execute("""
                INSERT INTO work_records (
                    record_type, week_start_date, week_end_date, record_status, meta_json, created_by
                ) VALUES (%s, %s, %s, %s, %s, %s)
            """, ('SCHEDULE_PERIOD', w_start, w_end, w_status, json.dumps(meta_json), 1))
            period_id = cursor.lastrowid
            
            # For each day of the week
            current_day = w_start
            while current_day <= w_end:
                for shift_name, s_time, e_time in shifts_def:
                    # Create Shift Slot
                    cursor.execute("""
                        INSERT INTO work_records (
                            parent_id, record_type, work_date, shift_name, 
                            start_time, end_time, required_headcount, record_status
                        ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
                    """, (period_id, 'SHIFT_SLOT', current_day, shift_name, s_time, e_time, 2, 'OPEN'))
                    slot_id = cursor.lastrowid
                    
                    if w_status == 'PUBLISHED':
                        # Past weeks: Assign random 2 employees (who are not on leave on this day)
                        day_employees = [emp for emp in employee_ids if not is_on_leave(emp, current_day)]
                        if len(day_employees) >= 2:
                            assigned_emps = random.sample(day_employees, 2)
                        else:
                            assigned_emps = day_employees
                            
                        for emp_id in assigned_emps:
                            # 1. Create Assignment
                            cursor.execute("""
                                INSERT INTO work_records (
                                    parent_id, record_type, employee_id, work_date, 
                                    shift_name, start_time, end_time, record_status
                                ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
                            """, (slot_id, 'ASSIGNMENT', emp_id, current_day, shift_name, s_time, e_time, 'ASSIGNED'))
                            assignment_id = cursor.lastrowid
                            
                            # 2. Create Attendance (only for days up to today: May 21, 2026)
                            if current_day <= date(2026, 5, 21):
                                # Determine actual clock in and clock out
                                s_dt = datetime.combine(current_day, datetime.strptime(s_time, '%H:%M:%S').time())
                                e_dt = datetime.combine(current_day, datetime.strptime(e_time, '%H:%M:%S').time())
                                if shift_name == 'Ca Đêm':
                                    e_dt = e_dt + timedelta(days=1)
                                    
                                # Randomize clock-in (some on-time, some late, some early)
                                late_min = 0
                                clock_in_offset = random.randint(-15, 25) # in minutes
                                check_in = s_dt + timedelta(minutes=clock_in_offset)
                                if clock_in_offset > 0:
                                    late_min = clock_in_offset
                                    
                                # Randomize clock-out
                                clock_out_offset = random.randint(-10, 45) # in minutes
                                check_out = e_dt + timedelta(minutes=clock_out_offset)
                                overtime_min = 0
                                if clock_out_offset > 0:
                                    overtime_min = clock_out_offset
                                    
                                total_work_min = int((check_out - check_in).total_seconds() / 60) - 30 # minus 30m break
                                if total_work_min < 0:
                                    total_work_min = 0
                                    
                                is_night = 1 if shift_name == 'Ca Đêm' else 0
                                
                                cursor.execute("""
                                    INSERT INTO work_records (
                                        parent_id, record_type, employee_id, work_date,
                                        shift_name, start_time, end_time, check_in_at, check_out_at,
                                        worked_minutes, late_minutes, overtime_minutes, is_night_shift,
                                        record_status, source
                                    ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
                                """, (
                                    assignment_id, 'ATTENDANCE', emp_id, current_day,
                                    shift_name, s_time, e_time, check_in, check_out,
                                    total_work_min, late_min, overtime_min, is_night,
                                    'PRESENT', 'BIOMETRIC'
                                ))
                                
                        # Update slot status
                        if len(assigned_emps) >= 2:
                            cursor.execute("UPDATE work_records SET record_status = 'FILLED' WHERE id = %s", (slot_id,))
                        elif len(assigned_emps) > 0:
                            cursor.execute("UPDATE work_records SET record_status = 'UNDERSTAFFED' WHERE id = %s", (slot_id,))
                            
                    elif w_status == 'REGISTRATION_OPEN':
                        # Future week: create mock registrations for employees
                        reg_candidates = random.sample(employee_ids, 8)
                        for emp_id in reg_candidates:
                            # employee registers preference for some shifts
                            if random.random() > 0.4:
                                cursor.execute("""
                                    INSERT INTO work_records (
                                        parent_id, record_type, employee_id, work_date,
                                        shift_name, start_time, end_time, preference_level, record_status
                                    ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
                                """, (slot_id, 'REGISTRATION', emp_id, current_day, shift_name, s_time, e_time, random.choice([1, 2, 3]), 'PENDING'))
                                
                current_day = current_day + timedelta(days=1)
                
        conn.commit()
        print("Da tao chu ky lich lam viec va cham cong xong.")

        # ---------------------------------------------------------
        # 5. Seed payroll_records (April 2026 payroll)
        # ---------------------------------------------------------
        print("Dang tao ky luong va phieu luong thang 04/2026...")
        # Create April 2026 Payroll Period
        cursor.execute("""
            INSERT INTO payroll_records (
                record_type, name, period_start, period_end, status, created_by
            ) VALUES (%s, %s, %s, %s, %s, %s)
        """, ('PERIOD', 'Kỳ lương tháng 04/2026', date(2026, 4, 1), date(2026, 4, 30), 'PUBLISHED', 1))
        payroll_period_id = cursor.lastrowid
        
        # Calculate and generate payslips for all employees for April 2026
        # Let's query base_salary, hourly_rate, full_name, role from users
        cursor.execute("SELECT id, full_name, base_salary, hourly_rate, employment_type FROM users WHERE role = 'EMPLOYEE'")
        employees_list = cursor.fetchall()
        
        for emp_id, full_name, base_sal, hr_rate, emp_type in employees_list:
            base_sal = float(base_sal) if base_sal is not None else 0.0
            hr_rate = float(hr_rate) if hr_rate is not None else 0.0
            # Query attendance logs for this employee in April 2026
            cursor.execute("""
                SELECT SUM(worked_minutes) as tot_work, SUM(late_minutes) as tot_late, 
                       SUM(overtime_minutes) as tot_ot, COUNT(*) as tot_shifts
                FROM work_records
                WHERE record_type = 'ATTENDANCE' 
                  AND employee_id = %s 
                  AND work_date BETWEEN '2026-04-01' AND '2026-04-30'
            """, (emp_id,))
            att_summary = cursor.fetchone()
            
            worked_min = float(att_summary[0]) if att_summary[0] is not None else 0.0
            late_min = float(att_summary[1]) if att_summary[1] is not None else 0.0
            overtime_min = float(att_summary[2]) if att_summary[2] is not None else 0.0
            shifts_count = int(att_summary[3]) if att_summary[3] is not None else 0
            
            # Computation
            if emp_type == 'FULL_TIME':
                base_amt = base_sal
            else:
                base_amt = round((worked_min / 60) * hr_rate, 0)
                
            # Overtime is 1.5x hourly rate
            hourly_val = hr_rate if hr_rate > 0 else (base_sal / 160)
            ot_amt = round((overtime_min / 60) * hourly_val * 1.5, 0)
            
            allowance_amt = 500000.0 # general allowance
            
            # Deduct 2,000 VND per minute late
            deduct_amt = float(late_min * 2000)
            
            final_amt = base_amt + ot_amt + allowance_amt - deduct_amt
            if final_amt < 0:
                final_amt = 0.0
                
            calculation_snapshot = {
                'worked_minutes': int(worked_min),
                'overtime_minutes': int(overtime_min),
                'late_minutes': int(late_min),
                'hourly_rate': float(hourly_val),
                'shifts_worked': int(shifts_count),
                'base_calculation': emp_type
            }
            
            cursor.execute("""
                INSERT INTO payroll_records (
                    parent_id, record_type, employee_id, name, period_start, period_end,
                    status, base_amount, overtime_amount, allowance_amount, deduction_amount,
                    final_amount, calculation_snapshot_json
                ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
            """, (
                payroll_period_id, 'ITEM', emp_id, f"Phiếu lương {full_name} - T4/2026",
                date(2026, 4, 1), date(2026, 4, 30), 'PUBLISHED',
                base_amt, ot_amt, allowance_amt, deduct_amt, final_amt,
                json.dumps(calculation_snapshot)
            ))
            
        conn.commit()
        print("Da tao ky luong va phieu luong thang 04/2026 xong.")

        # ---------------------------------------------------------
        # 6. Seed system_records (TICKET, AUDIT, AI_REPORT)
        # ---------------------------------------------------------
        print("Dang tao tickets khieu nai...")
        # Get some payroll items to link tickets to
        cursor.execute("SELECT id, employee_id, name FROM payroll_records WHERE record_type = 'ITEM' LIMIT 5")
        payslips = cursor.fetchall()
        
        ticket_titles = [
            "Sai lệch giờ công tăng ca",
            "Thiếu tiền phụ cấp chuyên cần",
            "Bị trừ tiền đi muộn nhầm ngày 15/4",
            "Khiếu nại giờ làm ca đêm",
            "Chưa nhận được phiếu lương chi tiết"
        ]
        ticket_details = [
            "Tôi thấy giờ tăng ca ngày thứ 7 tuần trước chưa được tính đủ. Kính mong HR kiểm tra lại.",
            "Tháng này tôi đi làm đầy đủ nhưng phiếu lương ghi thiếu 500k phụ cấp. Cảm ơn HR.",
            "Ngày 15/4 tôi check-in đúng giờ nhưng hệ thống báo muộn 15 phút và trừ tiền lương.",
            "Số giờ làm ca đêm của tôi bị tính thiếu 1 tiếng so với thực tế tôi làm.",
            "Hệ thống thông báo đã có phiếu lương nhưng tôi chưa thấy chi tiết hiển thị."
        ]
        
        for i, (payslip_id, emp_id, name) in enumerate(payslips):
            t_status = random.choice(['OPEN', 'IN_PROGRESS', 'RESOLVED'])
            handled_by = 1 if t_status != 'OPEN' else None
            handled_at = datetime.now() - timedelta(days=1) if t_status != 'OPEN' else None
            
            cursor.execute("""
                INSERT INTO system_records (
                    record_type, employee_id, related_entity_type, related_entity_id,
                    status, title, description, handled_by, handled_at
                ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
            """, (
                'TICKET', emp_id, 'payroll_period', payslip_id,
                t_status, ticket_titles[i], ticket_details[i], handled_by, handled_at
            ))
            
        # Create Audits
        print("Dang tao audit logs...")
        audits = [
            ('LOGIN', 'Đăng nhập hệ thống thành công', 1),
            ('USER_CREATE', 'Tạo tài khoản nhân viên mới EMP0004', 1),
            ('PAYROLL_CALCULATE', 'Chạy tính lương kỳ tháng 04/2026', 1),
            ('SCHEDULE_PUBLISH', 'Công bố lịch phân ca tuần 18/05 - 24/05', 1),
            ('LEAVE_APPROVE', 'Phê duyệt đơn xin nghỉ phép của Trần Văn An', 1),
            ('TICKET_RESOLVE', 'Giải quyết khiếu nại lương của nhân viên ID 2', 1)
        ]
        for action, desc, actor in audits:
            cursor.execute("""
                INSERT INTO system_records (
                    record_type, actor_user_id, action, description
                ) VALUES (%s, %s, %s, %s)
            """, ('AUDIT', actor, action, desc))
            
        # Create AI Reports
        print("Dang tao AI Reports...")
        
        # 1. Weekly report snapshot
        weekly_summary_input = {
            'week_start': '2026-05-11',
            'week_end': '2026-05-17',
            'total_employees': 18,
            'attendance_rows': 72,
            'total_hours': 576.5,
            'total_overtime': 42.0,
            'pending_leaves': 1
        }
        weekly_summary_output = {
            'summary': {
                'compliance_rate': '96%',
                'overtime_trend': 'Tăng 8% so với tuần trước',
                'burnout_risk_count': 1
            },
            'text_report': "Báo cáo vận hành tuần từ 11/05 đến 17/05:\n- Tổng số nhân sự hoạt động: 18.\n- Hiệu suất chấm công đạt mức cao (96% đúng giờ). Có ghi nhận 3 trường hợp đi muộn nhẹ dưới 10 phút.\n- Tổng thời gian tăng ca tăng nhẹ do nhu cầu hoàn thành dự án gấp.\n- Đề xuất: Cần chú ý phân bổ ca hợp lý hơn cho nhân viên bộ phận Kỹ thuật để tránh kiệt sức."
        }
        cursor.execute("""
            INSERT INTO system_records (
                record_type, actor_user_id, title, input_snapshot_json, output_json, model_name
            ) VALUES (%s, %s, %s, %s, %s, %s)
        """, ('AI_REPORT', 1, 'WEEKLY_REPORT', json.dumps(weekly_summary_input), json.dumps(weekly_summary_output), 'gemini-2.0-flash'))
        
        # 2. Burnout Prediction
        burnout_input = [
            {'employee_id': 2, 'week': '2026-W20', 'hours': 48.5, 'overtime_hours': 10.5, 'night_shifts': 2, 'late_count': 0},
            {'employee_id': 3, 'week': '2026-W20', 'hours': 40.0, 'overtime_hours': 2.0, 'night_shifts': 0, 'late_count': 1},
            {'employee_id': 4, 'week': '2026-W20', 'hours': 52.0, 'overtime_hours': 14.0, 'night_shifts': 3, 'late_count': 0}
        ]
        burnout_output = [
            {
                'employee_id': 4,
                'risk': 'HIGH',
                'reason': 'Làm việc vượt quá 50 giờ/tuần, có 3 ca đêm và lượng giờ làm tăng ca chiếm tỷ lệ cao trong tuần.',
                'suggestion': 'Giảm bớt ca đêm trong tuần tới, bố trí nghỉ bù ít nhất 1.5 ngày liên tục.'
            },
            {
                'employee_id': 2,
                'risk': 'MEDIUM',
                'reason': 'Thời gian làm việc ở mức cao (48.5h), có ca đêm và tăng ca tương đối.',
                'suggestion': 'Hạn chế tăng ca thêm trong tuần tiếp theo.'
            },
            {
                'employee_id': 3,
                'risk': 'LOW',
                'reason': 'Các chỉ số hoạt động nằm trong giới hạn an toàn thông thường.',
                'suggestion': 'Không có đề xuất đặc biệt.'
            }
        ]
        cursor.execute("""
            INSERT INTO system_records (
                record_type, actor_user_id, title, input_snapshot_json, output_json, model_name
            ) VALUES (%s, %s, %s, %s, %s, %s)
        """, ('AI_REPORT', 1, 'BURNOUT_PREDICTION', json.dumps(burnout_input), json.dumps(burnout_output), 'gemini-2.0-flash'))
        
        # 3. Smart Scheduler
        scheduler_input = {
            'week_start': '2026-05-25',
            'week_end': '2026-05-31',
            'shifts': [
                {'name': 'Ca Sáng', 'start': '06:00', 'end': '14:00', 'headcount': 2},
                {'name': 'Ca Chiều', 'start': '14:00', 'end': '22:00', 'headcount': 2},
                {'name': 'Ca Đêm', 'start': '22:00', 'end': '06:00', 'headcount': 1}
            ]
        }
        scheduler_output = {
            'assignments': [
                {'date': '2026-05-25', 'shift': 'Ca Sáng', 'employee_id': 2},
                {'date': '2026-05-25', 'shift': 'Ca Sáng', 'employee_id': 3},
                {'date': '2026-05-25', 'shift': 'Ca Chiều', 'employee_id': 5},
                {'date': '2026-05-25', 'shift': 'Ca Chiều', 'employee_id': 6},
                {'date': '2026-05-25', 'shift': 'Ca Đêm', 'employee_id': 7}
            ],
            'warnings': ["Thiếu người cho Ca Đêm ngày 30/05 do nhân viên xin nghỉ phép nhiều."],
            'explanation': "Lịch đề xuất ưu tiên phân bổ nhân sự có kinh nghiệm vào ca đêm đầu tuần và luân chuyển ca đều đặn."
        }
        cursor.execute("""
            INSERT INTO system_records (
                record_type, actor_user_id, title, input_snapshot_json, output_json, model_name
            ) VALUES (%s, %s, %s, %s, %s, %s)
        """, ('AI_REPORT', 1, 'SMART_SCHEDULER', json.dumps(scheduler_input), json.dumps(scheduler_output), 'gemini-2.0-flash'))

        conn.commit()
        print("Da tao system_records (AI reports & audits) xong.")
        print("DATABASE SEEDING HOAN THANH MY MAN!")

    except mysql.connector.Error as err:
        print(f"Loi Database: {err}")
    except Exception as e:
        print(f"Co loi xay ra: {e}")
    finally:
        if 'conn' in locals() and conn.is_connected():
            cursor.close()
            conn.close()
            print("Da dong ket noi database.")

if __name__ == "__main__":
    seed_database()
