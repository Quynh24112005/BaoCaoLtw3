import mysql.connector
from faker import Faker
import random
from datetime import timedelta

# Khởi tạo Faker với dữ liệu tiếng Việt
fake = Faker('vi_VN')

# Cấu hình kết nối MySQL (Bạn hãy thay đổi user/password cho phù hợp với máy của mình)
db_config = {
    'host': 'localhost',
    'user': 'root',
    'password': '2411', # <-- Thay đổi mật khẩu tại đây
    'database': 'RestaurantManagement'
}

def generate_and_insert_data():
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor()
        print("Da ket noi toi co so du lieu thanh cong!")

        num_records = 200

        # ==========================================
        # 1. Insert dữ liệu vào bảng tblShift
        # ==========================================
        
        print("Dang tao du lieu cho tblShift...")
        shift_data = []
        for _ in range(num_records):
            shift_date = fake.date_between(start_date='-1y', end_date='today')
            shift_num = random.randint(1, 3)
            # Tạo giờ bắt đầu ngẫu nhiên trong ngày, ca làm 8 tiếng
            start_time = fake.date_time_between_dates(datetime_start=shift_date, datetime_end=shift_date + timedelta(days=1))
            end_time = start_time + timedelta(hours=8)
            description = fake.text(max_nb_chars=100)
            
            shift_data.append((shift_date, shift_num, start_time, end_time, description))

        cursor.executemany("""
            INSERT INTO tblShift (date, shiftNumber, startTime, endTime, description)
            VALUES (%s, %s, %s, %s, %s)
        """, shift_data)


        # ==========================================
        # 2. Insert dữ liệu vào bảng tblPayslip
        # ==========================================
        print("Dang tao du lieu cho tblPayslip...")
        payslip_data = []
        for _ in range(num_records):
            start_date = fake.date_between(start_date='-1y', end_date='today')
            end_date = start_date + timedelta(days=7)
            overtime_rate = round(random.uniform(1.2, 2.0), 2)
            hourly_rate = round(random.uniform(20000, 50000), 0) # Lương theo giờ VNĐ
            
            payslip_data.append((start_date, end_date, overtime_rate, hourly_rate))

        cursor.executemany("""
            INSERT INTO tblPayslip (weekStartDate, weekEndDate, overtimeBonusRate, hourlyRate)
            VALUES (%s, %s, %s, %s)
        """, payslip_data)


        # ==========================================
        # 3. Insert dữ liệu vào bảng tblEmployee
        # ==========================================
        print("Dang tao du lieu cho tblEmployee...")
        employee_data = []
        roles = ['Manager', 'Chef', 'Cashier', 'Waiter', 'Security', 'Janitor']
        for i in range(num_records):
            rest_name = f"Nhà hàng {fake.company()}"
            rest_addr = fake.address()
            rest_desc = fake.catch_phrase()
            
            username = fake.user_name()[:25]
            password = fake.password(length=12)
            role = random.choice(roles)
            
            code = f"EMP{str(i+1).zfill(5)}"
            full_name = fake.name()
            phone = fake.phone_number()[:15]
            email = fake.ascii_company_email()
            dob = fake.date_of_birth(minimum_age=18, maximum_age=60)
            emp_desc = fake.job()

            employee_data.append((
                rest_name, rest_addr, rest_desc, 
                username, password, role, 
                code, full_name, phone, email, dob, emp_desc
            ))

        cursor.executemany("""
            INSERT INTO tblEmployee (
                restaurantName, restaurantAddress, restaurantDescription, 
                username, password, role, 
                code, fullName, phoneNumber, email, dob, employeeDescription
            ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        """, employee_data)


        # ==========================================
        # 4. Insert dữ liệu vào bảng tblRegistrationShiftRecord
        # ==========================================
        print("Dang tao du lieu cho tblRegistrationShiftRecord...")
        record_data = []
        statuses = ['Pending', 'Approved', 'Rejected', 'Completed', 'Cancelled']
        
        for _ in range(num_records):
            # Lấy ngẫu nhiên ID từ 1 đến 200 (vì các bảng kia vừa tạo 200 records)
            emp_id = random.randint(1, num_records)
            shift_id = random.randint(1, num_records)
            payslip_id = random.randint(1, num_records)
            
            reg_time = fake.past_datetime(start_date="-1y")
            reg_desc = fake.sentence()
            reg_note = fake.sentence()
            
            assigned_time = reg_time + timedelta(hours=random.randint(1, 48))
            assigned_amount = random.randint(1, 5)
            assigned_desc = fake.sentence()
            assigned_note = fake.sentence()
            status = random.choice(statuses)
            
            check_in = assigned_time + timedelta(days=random.randint(1, 10))
            check_out = check_in + timedelta(hours=8)

            record_data.append((
                emp_id, shift_id, 
                reg_time, reg_desc, reg_note, 
                assigned_time, assigned_amount, assigned_desc, assigned_note, status, 
                payslip_id, check_in, check_out
            ))

        cursor.executemany("""
            INSERT INTO tblRegistrationShiftRecord (
                employeeID, shiftID, 
                registeredTime, registrationDescription, registrationNote, 
                assignedTime, assignedAmount, assignedDescription, assignedNote, status, 
                payslipID, checkInTime, checkOutTime
            ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        """, record_data)

        # Commit toàn bộ thay đổi
        conn.commit()
        print("Hoan tat! Da them thanh cong 200 records cho moi bang.")

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
    generate_and_insert_data()



