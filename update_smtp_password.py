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
                    
                    print("[AI] Backing up .env file...")
                    os.write(fd, b"cp .env .env.bak_smtp\n")
                    time.sleep(1)

                    print("[AI] Updating MAIL settings in .env...")
                    # Update each key individually using sed
                    os.write(fd, b"sed -i 's/^MAIL_MAILER=.*/MAIL_MAILER=smtp/' .env\n")
                    os.write(fd, b"sed -i 's/^MAIL_HOST=.*/MAIL_HOST=smtp.gmail.com/' .env\n")
                    os.write(fd, b"sed -i 's/^MAIL_PORT=.*/MAIL_PORT=587/' .env\n")
                    os.write(fd, b"sed -i 's/^MAIL_USERNAME=.*/MAIL_USERNAME=support@boxleocourier.com/' .env\n")
                    os.write(fd, b"sed -i 's/^MAIL_PASSWORD=.*/MAIL_PASSWORD=\"ufjt tpzb mogh xkjn\"/' .env\n")
                    os.write(fd, b"sed -i 's/^MAIL_ENCRYPTION=.*/MAIL_ENCRYPTION=tls/' .env\n")
                    os.write(fd, b"sed -i 's/^MAIL_FROM_ADDRESS=.*/MAIL_FROM_ADDRESS=support@boxleocourier.com/' .env\n")
                    time.sleep(2)
                    
                    print("[AI] Clearing Laravel caches...")
                    os.write(fd, b"php artisan config:clear\n")
                    time.sleep(1)
                    os.write(fd, b"php artisan cache:clear\n")
                    time.sleep(1)
                    
                    print("[AI] Configuration updated. Exiting...")
                    os.write(fd, b"exit\n")
                    commands_sent = True
            
        except Exception as e:
            print(f"Error: {e}")
        finally:
            os.close(fd)
            os.waitpid(pid, 0)

if __name__ == "__main__":
    main()
