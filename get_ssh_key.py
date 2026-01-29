
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
        check_sent = False
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
                
                elif password_sent and not check_sent and ("master" in log or "$" in log):
                    time.sleep(1)
                    
                    # Check for key, generate if missing, and cat the public key
                    cmd = "if [ ! -f ~/.ssh/id_ed25519 ]; then ssh-keygen -t ed25519 -C 'deploy_key' -N '' -f ~/.ssh/id_ed25519; fi && cat ~/.ssh/id_ed25519.pub\n"
                    
                    os.write(fd, cmd.encode())
                    time.sleep(2)
                    os.write(fd, b"exit\n")
                    check_sent = True

        except Exception as e:
            print(f"Error: {e}")
        finally:
            os.close(fd)
            os.waitpid(pid, 0)

if __name__ == "__main__":
    main()
