
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
        update_sent = False
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
                    os.write(fd, b"REDACTED_SSH_PASSWORD\n")
                    password_sent = True
                    log = ""
                
                elif password_sent and not update_sent and ("master" in log or "$" in log):
                    time.sleep(2)
                    print("\n[AI] Pulling latest code and rebuilding...")
                    
                    cmds = [
                        "cd applications/zwpneuuzgz/public_html",
                        "git pull origin main",
                        "export NODE_OPTIONS=--max-old-space-size=4096",
                        "npm run build",
                        "ls -l public/build/assets/ | grep \"bust.js\"", # Verify the new file naming
                        "exit"
                    ]
                    
                    for cmd in cmds:
                        os.write(fd, (cmd + "\n").encode())
                        time.sleep(3)
                    
                    update_sent = True

        except Exception as e:
            print(f"Error: {e}")
        finally:
            os.close(fd)
            os.waitpid(pid, 0)

if __name__ == "__main__":
    main()
