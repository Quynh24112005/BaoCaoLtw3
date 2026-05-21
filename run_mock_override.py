from importlib import util
import sys
p = r'd:\PTIT\NAM3\KI-2\LTW\PHP\mock-data.py'
spec = util.spec_from_file_location('mock_data', p)
module = util.module_from_spec(spec)
spec.loader.exec_module(module)
# Inject password
if isinstance(module, dict):
    pass
try:
    module.db_config['password'] = '2411'
except Exception:
    pass
# Call the function
module.generate_and_insert_data()
