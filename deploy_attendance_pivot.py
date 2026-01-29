
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
    
    # Using a deterministic temp filename based on extension
    ext = os.path.splitext(remote_path)[1].replace('.', '')
    temp_file = f"/tmp/patch_{ext}.b64"
    
    os.write(fd, f"cat > {temp_file} << 'EOF'\n".encode())
    chunk_size = 1000
    for i in range(0, len(encoded_content), chunk_size):
        os.write(fd, encoded_content[i:i+chunk_size].encode('utf-8'))
        time.sleep(0.05)
    os.write(fd, b"\nEOF\n")
    time.sleep(1)
    
    # Decode and overwrite
    cmd = f"base64 -d {temp_file} > {remote_path} && rm {temp_file} && echo 'PATCH_OK_{os.path.basename(remote_path)}'\n"
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
                    
                    # 1. Performance Controller (Backend Logic)
                    patch_file(fd, '/home/el/work/boxleo/app/Http/Controllers/Api/PerformanceApiEvaluation.php', 
                               'applications/zwpneuuzgz/public_html/app/Http/Controllers/Api/PerformanceApiEvaluation.php')
                    
                    # 2. Performance Page (Revert to original)
                    patch_file(fd, '/home/el/work/boxleo/resources/js/components/performance/PerformanceEvaluation.vue', 
                               'applications/zwpneuuzgz/public_html/resources/js/components/performance/PerformanceEvaluation.vue')
                    
                    # 3. Attendance Page (New Visual Card)
                    patch_file(fd, '/home/el/work/boxleo/resources/js/components/attendances/Attendances.vue', 
                               'applications/zwpneuuzgz/public_html/resources/js/components/attendances/Attendances.vue')
                    
                    # 4. API Routes (Registration)
                    patch_file(fd, '/home/el/work/boxleo/routes/api.php', 
                               'applications/zwpneuuzgz/public_html/routes/api.php')
                    
                    print("\n[AI] All patches sent. Starting remote build...")
                    
                    # Trigger build
                    os.write(fd, b"cd applications/zwpneuuzgz/public_html && npm run build\n")
                    
                    time.sleep(40) # Wait for build to reasonably progress
                    os.write(fd, b"exit\n")
                    patches_sent = True

        except Exception as e:
            print(f"Error: {e}")
        finally:
            os.close(fd)
            os.waitpid(pid, 0)

if __name__ == "__main__":
    main()
