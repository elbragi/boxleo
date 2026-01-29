
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
        fix_sent = False
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
                
                elif password_sent and not fix_sent and ("master" in log or "$" in log):
                    time.sleep(2)
                    print("\n[AI] Fixing Git permissions and re-syncing...")
                    
                    cmds = [
                        "git config --global --add safe.directory /mnt/data/home/1552216.cloudwaysapps.com/zwpneuuzgz/public_html",
                        "cd applications/zwpneuuzgz/public_html",
                        "git reset --hard origin/main",
                        "git pull origin main",
                        "export NODE_OPTIONS=--max-old-space-size=4096",
                        "npm run build",
                        "exit"
                    ]
                    
                    for cmd in cmds:
                        os.write(fd, (cmd + "\n").encode())
                        time.sleep(2)
                    
                    fix_sent = True

        except Exception as e:
            print(f"Error: {e}")
        finally:
            os.close(fd)
            os.waitpid(pid, 0)

if __name__ == "__main__":
    main()
