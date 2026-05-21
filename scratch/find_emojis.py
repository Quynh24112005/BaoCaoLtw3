import os
import re

# Simple regex range for emojis
emoji_pattern = re.compile(
    r'[\U00010000-\U0010ffff]|'  # Unicode emoji planes
    r'[\u2600-\u27BF]|'          # Miscellaneous Symbols and Dingbats
    r'[\u2300-\u23FF]'           # Miscellaneous Technical
)

views_dir = r"d:\PTIT\NAM3\KI-2\LTW\PHP\app\views"
found_emojis = []

for root, dirs, files in os.walk(views_dir):
    for file in files:
        if file.endswith('.php'):
            file_path = os.path.join(root, file)
            try:
                with open(file_path, 'r', encoding='utf-8') as f:
                    for line_num, line in enumerate(f, 1):
                        matches = emoji_pattern.findall(line)
                        if matches:
                            found_emojis.append({
                                'file': file_path,
                                'line': line_num,
                                'emojis': matches,
                                'content': line.strip()
                            })
            except Exception as e:
                pass

output_path = r"d:\PTIT\NAM3\KI-2\LTW\PHP\scratch\emojis_found.txt"
with open(output_path, 'w', encoding='utf-8') as out:
    out.write(f"Found {len(found_emojis)} lines with emojis:\n")
    for item in found_emojis:
        rel_path = os.path.relpath(item['file'], views_dir)
        out.write(f"File: app/views/{rel_path.replace(os.sep, '/')}\n")
        out.write(f"Line {item['line']}: {item['emojis']} -> {item['content']}\n")
        out.write("-" * 50 + "\n")

print(f"Done, written {len(found_emojis)} results to {output_path}")
