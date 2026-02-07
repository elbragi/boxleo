
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
        commands_sent = False
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
                
                elif password_sent and not commands_sent and ("master" in log or "$" in log):
                    time.sleep(1)
                    print("\n[AI] Navigating to application directory...")
                    os.write(fd, b"cd applications/zwpneuuzgz/public_html\n")
                    time.sleep(1)
                    
                    print("[AI] Granting approve_leave_applications permission to Billy Pacho...")
                    os.write(fd, b"php artisan tinker --execute=\"App\\\\Models\\\\User::find(35)->givePermissionTo('approve_leave_applications'); echo 'Done';\"\n")
                    time.sleep(5)
                    
                    print("[AI] Deployment complete. Exiting...")
                    os.write(fd, b"exit\n")
                    commands_sent = True

        except Exception as e:
            print(f"Error: {e}")
        finally:
            os.close(fd)
            os.waitpid(pid, 0)

if __name__ == "__main__":
    main()
