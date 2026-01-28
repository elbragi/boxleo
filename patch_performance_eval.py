
import pty
import os
import sys
import select
import time
import base64

def patch_file(fd, local_path, remote_path):
    with open(local_path, 'rb') as f:
        content = f.read()
    encoded_content = base64.b64encode(content).decode('utf-8')
    
    print(f"\n[AI] Sending patch to {remote_path}...")
    
    # Clear temp file
    os.write(fd, b"cat > /tmp/patch.b64 << 'EOF'\n")
    chunk_size = 1000
    for i in range(0, len(encoded_content), chunk_size):
        os.write(fd, encoded_content[i:i+chunk_size].encode('utf-8'))
        time.sleep(0.05)
    os.write(fd, b"\nEOF\n")
    time.sleep(1)
    
    # Decode and overwrite
    cmd = f"base64 -d /tmp/patch.b64 > {remote_path} && rm /tmp/patch.b64 && echo 'PATCH_OK_{os.path.basename(remote_path)}'\n"
    os.write(fd, cmd.encode('utf-8'))
    time.sleep(1)

def main():
    pid, fd = pty.fork()
    
    if pid == 0:
        os.execlp('ssh', 'ssh', '-o', 'StrictHostKeyChecking=no', 'master_xzpwmmwvbr@52.70.83.56')
    else:
        password_sent = False
        patches_sent = False
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
                
                elif password_sent and not patches_sent and ("master" in log or "$" in log):
                    time.sleep(2)
                    
                    # Patch Controller
                    patch_file(fd, '/home/el/work/boxleo/app/Http/Controllers/Api/PerformanceApiEvaluation.php', 
                               'applications/zwpneuuzgz/public_html/app/Http/Controllers/Api/PerformanceApiEvaluation.php')
                    
                    # Patch Routes
                    patch_file(fd, '/home/el/work/boxleo/routes/api.php', 
                               'applications/zwpneuuzgz/public_html/routes/api.php')
                    
                    time.sleep(2)
                    os.write(fd, b"exit\n")
                    patches_sent = True

        except Exception as e:
            print(f"Error: {e}")
        finally:
            os.close(fd)
            os.waitpid(pid, 0)

if __name__ == "__main__":
    main()
