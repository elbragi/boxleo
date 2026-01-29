
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
        git_cmds_sent = False
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
                
                elif password_sent and not git_cmds_sent and ("master" in log or "$" in log):
                    time.sleep(2)
                    print("\n[AI] Initializing Git on server...")
                    
                    # Sequence of commands to initialize and sync
                    cmds = [
                        "cd applications/zwpneuuzgz/public_html",
                        "git init",
                        "git remote add origin git@github.com:elbragi/boxleo.git",
                        "git fetch origin main",
                        "git reset --hard origin/main",
                        "git branch --set-upstream-to=origin/main main",
                        "echo 'GIT_INIT_SUCCESS'",
                        "npm run build", # Re-build to be safe
                        "exit"
                    ]
                    
                    for cmd in cmds:
                        os.write(fd, (cmd + "\n").encode())
                        time.sleep(3) # Wait between commands
                    
                    git_cmds_sent = True

        except Exception as e:
            print(f"Error: {e}")
        finally:
            os.close(fd)
            os.waitpid(pid, 0)

if __name__ == "__main__":
    main()
