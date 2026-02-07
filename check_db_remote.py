
import pty
import os
import sys
import select
import time

def main():
    pid, fd = pty.fork()
    
    if pid == 0:
        os.execlp('ssh', 'ssh', '-o', 'StrictHostKeyChecking=no', 'master_xzpwmmwvbr@52.70.83.56')
    else:
        password_sent = False
        query_sent = False
        log = ""
        
        try:
            while True:
                r, w, e = select.select([fd], [], [], 15)
                if not r: break
                try:
                    chunk = os.read(fd, 2048).decode('utf-8', 'ignore')
                except OSError: break
                if not chunk: break
                
                sys.stdout.write(chunk)
                sys.stdout.flush()
                log += chunk
                
                if not password_sent and ("password:" in log.lower()):
                    time.sleep(0.5)
                    os.write(fd, b"XeGPWXJg7vrU\n")
                    password_sent = True
                    log = ""
                
                elif password_sent and not query_sent and ("master" in log or "$" in log):
                    time.sleep(1)
                    # Search all tables for 'Rutendo'
                    query = """mysql -u zwpneuuzgz -p'KYxc5YdANG' zwpneuuzgz -N -e "SHOW TABLES" | while read table; do col=$(mysql -u zwpneuuzgz -p'KYxc5YdANG' zwpneuuzgz -N -e "DESCRIBE $table" | grep -Ei 'firstname|lastname|name|email' | cut -f1 | head -n 1); if [ ! -z "$col" ]; then count=$(mysql -u zwpneuuzgz -p'KYxc5YdANG' zwpneuuzgz -N -e "SELECT COUNT(*) FROM $table WHERE $col LIKE '%Rutendo%'"); if [ $count -gt 0 ]; then echo "Found in $table ($col): $count matches"; fi; fi; done\n"""
                    os.write(fd, query.encode('utf-8'))
                    time.sleep(10)
                    os.write(fd, b"exit\n")
                    query_sent = True

        except Exception as e:
            print(f"Error: {e}")
        finally:
            os.close(fd)
            os.waitpid(pid, 0)

if __name__ == "__main__":
    main()
