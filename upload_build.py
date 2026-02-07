
import pty
import os
import sys
import select
import time

def main():
    pid, fd = pty.fork()
    
    if pid == 0:
        # Use scp to copy the build folder
        os.execlp('scp', 'scp', '-r', 
                  '/home/el/work/boxleo/public/build',
                  'master_xzpwmmwvbr@52.70.83.56:applications/zwpneuuzgz/public_html/public/')
    else:
        password_sent = False
        log = ""
        
        try:
            while True:
                r, w, e = select.select([fd], [], [], 30)
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

        except Exception as e:
            print(f"Error: {e}")
        finally:
            os.close(fd)
            os.waitpid(pid, 0)

if __name__ == "__main__":
    main()
