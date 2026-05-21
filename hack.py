# coba perbaiki code ini, chat!
import os
import time
import random
import threading
import getpass
import datetime
import string

# Multi-frequency beep thread
def beep():
    while True:
        time.sleep(random.uniform(1.8, 9))
        for _ in range(random.randint(1,5)):
            print("\a", end="", flush=True)
            time.sleep(0.12)

def type_text(text, delay=0.02):
    for char in text:
        print(char, end="", flush=True)
        time.sleep(delay)
    print()

def glitch_text(text, iterations=3):
    glitch_chars = "█▓▒░!@#$%^&*"
    for _ in range(iterations):
        glitched = "".join(random.choice(glitch_chars) if random.random() > 0.7 else c for c in text)
        print(f"\r{glitched}", end="", flush=True)
        time.sleep(0.05)
    print(f"\r{text}", flush=True)

threading.Thread(target=beep, daemon=True).start()

try:
    os.system("mode 180, 55")
except Exception:
    pass
try:
    os.system('title ▓▓▓ OMEGA BREACH PROTOCOL v11.5 - TOTAL SYSTEM ANNIHILATION ▓▓▓')
except Exception:
    pass

user = getpass.getuser()
money = 0
iteration = 0
total_files_encrypted = 0
chars = "0123456789ABCDEF!@#$%^&*()_+-=[]{}|;:,.<>?/\\~`"
hex_chars = "0123456789ABCDEF"

malware_names = [
    "DARKCOMET", "NJRAT", "CERBERUS", "LOKIBOT", "EMOTET",
    "TRICKBOT", "RYUK", "REVIL", "CONTI", "BLACKMATTER"
]

while True:
    try:
        os.system("cls")
    except Exception:
        pass
    iteration += 1

    # ANIMATED SKULL ASCII
    print("\033[91m")
    print("                    ███████████████████████████████████████████████████████████████████████████")
    print("                    █░░░░░░░░░░░░░░█████████░░░░░░░░░░░░░░█████████░░░░░░░░░░░░░░█████████░░█")
    print("                    █░░██████░░░░░░█████████░░░░░░░░░░░░░░█████████░░░░░░░░░░░░░░█████████░░█")
    print("                    █░░██████░░░░░░█████████░░░░██████░░░░█████████░░░░██████░░░░█████████░░█")
    print("                    █░░░░░░░░░░░░░░███░░░███░░░░██████░░░░███░░░███░░░░██████░░░░███░░░███░░█")
    print("                    █░░██████████░░███░░░███░░░░░░░░░░░░░░███░░░███░░░░░░░░░░░░░░███░░░███░░█")
    print("                    █░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░█")
    print("                    ███████████████████████████████████████████████████████████████████████████")
    print("\033[0m")

    # MEGA HEADER
    print("\033[91m\033[5m" + "╔" + "═"*178 + "╗\033[0m")
    print("\033[91m\033[5m║" + "  ⚠ ⚠ ⚠  O M E G A   L E V E L   B R E A C H   -   S Y S T E M   D E S T R U C T I O N   I N   P R O G R E S S  ⚠ ⚠ ⚠  ".center(180) + "║\033[0m")
    print("\033[91m\033[5m║" + f"ITERATION #{iteration:06d} | DEFCON 1 | THREAT LEVEL: EXTINCTION | YOUR DATA IS OURS".center(180) + "║\033[0m")
    print("\033[91m\033[5m╚" + "═"*178 + "╝\033[0m")

    timestamp = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S.%f")[:-3]
    session_id = f"{random.randint(100000,999999)}-{''.join(random.choices(string.ascii_uppercase + string.digits, k=8))}"
    print(f"\033[90m[{timestamp}] TARGET: {user.upper()} | SESSION: {session_id} | PID: {random.randint(10000,99999)} | TID: {random.randint(1000,9999)}\033[0m\n")

    # SYSTEM COMPROMISE METRICS
    print("\033[96m" + "╔" + "═"*178 + "╗\033[0m")
    print("\033[96m║" + " SYSTEM COMPROMISE METRICS ".center(180) + "║\033[0m")
    print("\033[96m" + "╠" + "═"*178 + "╣\033[0m")

    compromise_level = min(iteration * 7, 100)
    bar = "█" * (compromise_level) + "░" * (100 - compromise_level)
    print(
        f"\033[91m║ COMPROMISE LEVEL: [{bar}] {compromise_level}% - {random.choice(['CATASTROPHIC','TERMINAL','CRITICAL','DEVASTATING'])}"
        + "\033[96m" + " " * (180 - len(f" COMPROMISE LEVEL: [{bar}] {compromise_level}% - CATASTROPHIC") - 2) + "║\033[0m"
    )

    print("\033[96m" + "╠" + "═"*178 + "╣\033[0m")
    print(f"\033[37m║ TARGET IDENTITY  │ {user.upper():<40} │ PRIVILEGE LEVEL │ {random.choice(['ADMINISTRATOR','SYSTEM','ROOT','DOMAIN ADMIN']):<30} ║")
    print(f"║ TARGET MACHINE   │ {os.environ.get('COMPUTERNAME', 'UNKNOWN'):<40} │ DOMAIN          │ {random.choice(['WORKGROUP','CORPORATE.LOCAL','PRODUCTION.NET']):<30} ║")
    print(f"║ PRIMARY IP       │ {random.randint(1,255)}.{random.randint(1,255)}.{random.randint(1,255)}.{random.randint(1,255):<37} │ GATEWAY         │ {random.randint(1,255)}.{random.randint(1,255)}.{random.randint(1,255)}.1{' '*25} ║")
    print(f"║ EXTERNAL IP      │ {random.randint(1,255)}.{random.randint(1,255)}.{random.randint(1,255)}.{random.randint(1,255):<37} │ PROXY CHAIN     │ {random.choice(['7 HOPS','11 HOPS','15 HOPS'])} via {random.choice(['TOR','I2P','VPN'])}{' '*15} ║")
    print(f"║ MAC ADDRESS      │ {':'.join([''.join(random.choices(hex_chars, k=2)) for _ in range(6)]):<40} │ NETWORK TYPE    │ {random.choice(['ETHERNET','WIFI 5GHZ','WIFI 2.4GHZ']):<30} ║")
    print(f"║ GEOLOCATION      │ {random.choice(['Jakarta Selatan','Shanghai','Moscow','Virginia','Frankfurt','Singapore','London','Dubai','Tokyo','Mumbai'])}, {random.choice(['Indonesia','China','Russia','USA','Germany','Singapore','UK','UAE','Japan','India']):<18} │ GPS COORDINATES │ {random.uniform(-90,90):>7.4f}, {random.uniform(-180,180):>8.4f}{' '*10} ║")
    print(f"║ ISP PROVIDER     │ {random.choice(['Telkom Indonesia','China Telecom','Rostelecom','Verizon','Deutsche Telekom','AWS','Google Cloud']):<40} │ CONNECTION      │ {random.randint(100,1000)} Mbps {random.choice(['FIBER','DSL','CABLE'])}{' '*13} ║")
    print("\033[96m" + "╠" + "═"*178 + "╣\033[0m")
    print(f"║ EXPLOIT VECTOR   │ {random.choice(['Log4Shell CVE-2021-44228','ProxyShell CVE-2021-34473','ZeroLogon CVE-2020-1472','PrintNightmare CVE-2021-34527','EternalBlue MS17-010','BlueKeep CVE-2019-0708']):<72} ║")
    print(f"║ ATTACK CHAIN     │ {random.choice(['Phishing → Macro → C2','Drive-by Download → Exploit Kit → RAT','USB Drop → AutoRun → Backdoor','Watering Hole → 0-Day → Implant']):<72} ║")
    print(f"║ PAYLOAD DELIVERY │ {random.choice(['PowerShell Empire','Cobalt Strike','Metasploit','Custom Malware']):<30} │ PERSISTENCE     │ {random.choice(['Registry RunOnce','Scheduled Task','WMI Event','Service Creation']):<30} ║")
    print(f"║ C2 SERVER        │ {random.choice(['tor3xh7d9ks.onion','185.220.101.XX','darknet-c2.ru','c2.evil-corp.xyz']):<40} │ PROTOCOL        │ {random.choice(['HTTPS','DNS','ICMP','SMB']):<30} ║")
    print(f"║ MALWARE FAMILY   │ {random.choice(malware_names):<40} │ VERSION         │ v{random.randint(1,9)}.{random.randint(0,9)}.{random.randint(0,99)}{' '*22} ║")
    print("\033[96m" + "╚" + "═"*178 + "╝\033[0m\n")

    # REAL-TIME SURVEILLANCE
    print("\033[92m" + "╔" + "═"*178 + "╗\033[0m")
    print("\033[92m║" + " REAL-TIME SURVEILLANCE & DATA EXFILTRATION ".center(180) + "║\033[0m")
    print("\033[92m" + "╠" + "═"*178 + "╣\033[0m")
    print(f"║ 📹 WEBCAM         │ \033[91m● LIVE 4K@60FPS\033[92m   │ 🎤 MICROPHONE    │ \033[91m● STEREO 48KHZ\033[92m    │ 🖥️  SCREEN        │ \033[91m● CAPTURE 60FPS\033[92m    │ ⌨️  KEYLOGGER    │ \033[91m● {random.randint(1247,19876)} KEYS\033[92m    ║")
    print(f"║ 📋 CLIPBOARD      │ {random.choice(['Bitcoin wallet','SSH private key','Master password','Credit card #']):<16} │ 🖱️  MOUSE TRACK   │ REAL-TIME COORD │ 📱 GPS LOCATION  │ ±{random.randint(3,87)}m ACCURACY   │ 📞 CALL LOG      │ {random.randint(47,312)} CALLS      ║")
    print(f"║ 📧 EMAIL SPY      │ {random.randint(234,987)} EMAILS       │ 💬 SMS INTERCEPT │ {random.randint(89,456)} MESSAGES    │ 📇 CONTACTS      │ {random.randint(178,876)} SYNCED      │ 📅 CALENDAR      │ {random.randint(23,145)} EVENTS     ║")
    print(f"║ 🌐 BROWSER        │ {random.randint(2847,9234)} SITES      │ 🍪 COOKIES       │ {random.randint(567,2341)} STOLEN      │ 📝 FORM DATA     │ {random.randint(34,198)} CREDS       │ 💾 DOWNLOADS     │ {random.randint(89,456)} FILES      ║")
    print(f"║ 📂 FILE SYSTEM    │ FULL ACCESS       │ 🔐 PASSWORDS     │ {random.randint(45,234)} EXTRACTED   │ 🎮 GAMING        │ STEAM/EPIC HACKED │ 💳 PAYMENT       │ {random.randint(3,9)} CARDS CLONED ║")
    print("\033[92m" + "╚" + "═"*178 + "╝\033[0m\n")

    # NETWORK TRAFFIC
    print("\033[93m" + "╔" + "═"*178 + "╗\033[0m")
    print("\033[93m║" + " NETWORK TRAFFIC ANALYSIS ".center(180) + "║\033[0m")
    print("\033[93m" + "╠" + "═"*178 + "╣\033[0m")
    upload_speed = random.uniform(127.5, 892.3)
    download_speed = random.uniform(45.2, 234.8)
    upload_bar = "█" * int(upload_speed / 10) + "░" * (90 - int(upload_speed / 10))
    download_bar = "█" * int(download_speed / 5) + "░" * (90 - int(download_speed / 5))
    print(f"║ ↑ UPLOAD    │ [{upload_bar[:70]}] {upload_speed:>6.2f} MB/s │ DEST: {random.choice(['tor3xh7d9ks.onion','darknet-exfil.ru','bitcoin-wallet.xyz']):<35} ║")
    print(f"║ ↓ DOWNLOAD  │ [{download_bar[:70]}] {download_speed:>6.2f} MB/s │ LOAD: {random.choice(['cryptominer.exe','ransomware.dll','backdoor.sh','rootkit.sys']):<35} ║")
    print(f"║ DNS QUERIES │ {random.randint(234,1876)}/sec      │ PACKETS │ {random.randint(125000,987654)} intercepted │ C2 HEARTBEAT │ \033[91m● {random.choice(['CONNECTED','SYNCING','UPLOADING'])}\033[93m              │ BANDWIDTH    │ {random.uniform(45.7,234.8):.1f} GB USED  ║")
    print("\033[93m" + "╚" + "═"*178 + "╝\033[0m\n")

    # FINANCIAL DEVASTATION
    money += random.randint(247_000_000, 1_984_000_000)
    print("\033[91m" + "╔" + "═"*178 + "╗\033[0m")
    print("\033[91m║" + " 💰 FINANCIAL DEVASTATION REPORT - YOUR WEALTH IS BEING DRAINED 💰 ".center(180) + "║\033[0m")
    print("\033[91m" + "╠" + "═"*178 + "╣\033[0m")
    print(f"║ BANK ACCOUNTS   │ ${money//1000000:>8,}.{money%1000000//100000:02d}M USD transferred to offshore account #{random.randint(100000000,999999999)}" + " " * 45 + "║")
    btc_amount = random.uniform(0.37, 24.84)
    eth_amount = random.uniform(11, 437)
    print(f"║ BITCOIN (BTC)   │ {btc_amount:>8.4f} BTC (~${btc_amount * random.uniform(42000, 68000):>12,.2f} USD) → Wallet: 1Grok{random.randint(10000,99999)}xX..." + " " * 48 + "║")
    print(f"║ ETHEREUM (ETH)  │ {eth_amount:>8.2f} ETH (~${eth_amount * random.uniform(2200, 3800):>12,.2f} USD) → Wallet: 0xDEADBEEF{random.randint(1000,9999)}..." + " " * 45 + "║")
    print(f"║ CREDIT CARDS    │ {random.randint(3,12)} cards compromised │ Fraudulent charges: ${random.randint(24000,189000):>8,} USD │ CVV: {random.randint(100,999)} │ Exp: {random.randint(1,12):02d}/{random.randint(25,30)}" + " " * 30 + "║")
    print(f"║ STOCK PORTFOLIO │ {random.randint(457,1876)} shares liquidated │ Total loss: ${random.randint(780000,4500000):>10,} USD │ Brokers: {random.choice(['E-Trade','Robinhood','Fidelity','TD Ameritrade'])}" + " " * 30 + "║")
    print(f"║ PAYPAL ACCOUNT  │ ${random.randint(4567,28934):>8,} USD drained │ VENMO ACCOUNT │ ${random.randint(1234,8765):>8,} USD drained │ STRIPE │ ${random.randint(8900,45678):>8,} USD" + " " * 23 + "║")
    print(f"║ RETIREMENT 401K │ ${random.randint(125000,987654):>10,} USD liquidated early (penalties waived by us, how nice!) " + " " * 60 + "║")
    print(f"║ TOTAL DAMAGE    │ \033[5m${(money//1000000) + random.randint(2,8):>8,} MILLION USD AND COUNTING...\033[0m\033[91m" + " " * 95 + "║")
    print("\033[91m" + "╚" + "═"*178 + "╝\033[0m\n")

    # ATTACK KILL CHAIN
    print("\033[95m" + "╔" + "═"*178 + "╗\033[0m")
    print("\033[95m║" + " ⚔️  CYBER KILL CHAIN - ADVANCED PERSISTENT THREAT (APT) STAGES ⚔️ ".center(180) + "║\033[0m")
    print("\033[95m" + "╠" + "═"*178 + "╣\033[0m")

    stages = [
        ("1️⃣  RECONNAISSANCE", ["Target profiling complete", "Social media scraped", "Email addresses harvested", "Network topology mapped"]),
        ("2️⃣  WEAPONIZATION", ["Zero-day exploit crafted", "Malicious payload encoded", "Polymorphic shellcode generated", "Anti-forensics enabled"]),
        ("3️⃣  DELIVERY", ["Spear-phishing email sent", "Malicious attachment opened", "Drive-by download triggered", "Watering hole compromised"]),
        ("4️⃣  EXPLOITATION", ["Vulnerability exploited", "Code execution achieved", "Buffer overflow successful", "RCE gained"]),
        ("5️⃣  INSTALLATION", ["Backdoor implanted", "Rootkit deployed", "Persistence established", "Anti-virus bypassed"]),
        ("6️⃣  COMMAND & CONTROL", ["C2 connection established", "Encrypted tunnel active", "Beaconing every 30s", "Remote access confirmed"]),
        ("7️⃣  PRIVILEGE ESCALATION", ["UAC bypass complete", "Token manipulation done", "SYSTEM privileges gained", "Domain admin compromised"]),
        ("8️⃣  DEFENSE EVASION", ["Windows Defender killed", "Firewall rules modified", "Event logs cleared", "EDR/XDR disabled"]),
        ("9️⃣  CREDENTIAL ACCESS", ["LSASS dumped", "SAM database cracked", "Kerberos tickets stolen", f"{random.randint(45,234)} passwords obtained"]),
        ("🔟 DISCOVERY", ["Network shares enumerated", "Active Directory queried", "Cloud resources discovered", "Backup systems located"]),
        ("1️⃣1️⃣ LATERAL MOVEMENT", ["SMB exploitation", "RDP hijacking", "PSExec deployment", "WMI remote execution"]),
        ("1️⃣2️⃣ COLLECTION", ["Sensitive files archived", "Database dumps created", "Screenshots captured", "Keystrokes logged"]),
        ("1️⃣3️⃣ EXFILTRATION", ["Data compressed", "Encryption applied", "C2 upload started", f"{random.uniform(47.5, 287.9):.1f} GB transferred"]),
        ("1️⃣4️⃣ IMPACT", ["Ransomware deployed", "Backups deleted", "System encrypted", "Ransom note displayed"])
    ]

    for stage, actions in stages:
        print(f"║ \033[93m{stage:<25}\033[95m │ ", end="")
        action_line = " → ".join(f"\033[92m✓\033[95m {action}" for action in actions)
        print(f"{action_line:<150}║")
        time.sleep(0.06)

    print("\033[95m" + "╚" + "═"*178 + "╝\033[0m\n")

    # MASSIVE MATRIX RAIN
    print("\033[92m" + "╔" + "═"*178 + "╗\033[0m")
    print("\033[92m║" + " ⚡ DATA STREAM INTERCEPTED - PACKETS CAPTURED - CREDENTIALS FLOWING ⚡ ".center(180) + "║\033[0m")
    print("\033[92m" + "╚" + "═"*178 + "╝\033[0m")

    for _ in range(25):
        line = "".join(random.choice(chars) for _ in range(178))
        color = random.choice(["\033[92m","\033[96m","\033[97m","\033[32m","\033[36m","\033[93m","\033[95m"])
        print(color + line + "\033[0m")
        time.sleep(0.025)
    print()

    # MEMORY HEXDUMP
    print("\033[90m" + "╔" + "═"*178 + "╗\033[0m")
    print("\033[90m║" + " 💾 MEMORY DUMP - SENSITIVE DATA LEAKED FROM RAM 💾 ".center(180) + "║\033[0m")
    print("\033[90m" + "╠" + "═"*178 + "╣\033[0m")

    for i in range(16):
        addr = f"0x{random.randint(0x7FF00000, 0x7FFFFFFF):08X}"
        hex_data = " ".join("".join(random.choices(hex_chars, k=2)) for _ in range(16))
        ascii_data = "".join(random.choice(chars) if random.random() > 0.6 else '.' for _ in range(16))
        annotation = random.choice(["<PASSWORD>", "<API_KEY>", "<SESSION>", "<CREDIT_CARD>", "<PRIVATE_KEY>", "<TOKEN>", ""])
        print(f"\033[90m║ {addr}  {hex_data}  |{ascii_data}|  {annotation:<20} ║\033[0m")

    print("\033[90m" + "╚" + "═"*178 + "╝\033[0m\n")

    # MEGA ALERT SYSTEM
    alerts = [
        "🔒 RANSOMWARE DEPLOYED - PAY 25 BTC TO 1GrokEvil69... OR ALL DATA DELETED IN 48 HOURS",
        "🔑 ALL PASSWORDS EXTRACTED - UPLOADED TO DARK WEB - CHECK HAVEIBEENPWNED.COM",
        "📹 WEBCAM FEED LIVE STREAMED TO 4CHAN /b/ - SMILE FOR THE CAMERA!",
        "🎤 AUDIO RECORDING: 'Ya Tuhan ampuni saya, laptop ini kena virus parah banget!'",
        "📸 10,000+ SCREENSHOTS UPLOADED TO MEGA.NZ - SHARED WITH YOUR CONTACT LIST",
        "⏰ WINDOWS WILL FORMAT IN 10 MINUTES - ALL PARTITIONS INCLUDING RECOVERY",
        "💳 BANK SMS: 'Transaksi Rp 847.500.000 ke REKENING ASING - BERHASIL'",
        "🤡 THIS IS A PRANK... JUST KIDDING, YOU'RE COMPLETELY PWNED BRO",
        "👾 GROK HACKING TEAM 2025 - YOUR SYSTEM BELONGS TO US NOW",
        "🌐 BROWSER HISTORY SENT TO: MOM, DAD, BOSS, GIRLFRIEND, AND FBI",
        "📧 YOUR PRIVATE EMAILS FORWARDED TO ENTIRE COMPANY - CHECK YOUR SENT FOLDER",
        "🔓 ALL ACCOUNTS POSTED ON PASTEBIN - GOOGLE YOUR USERNAME + 'LEAK'",
        "💀 UEFI ROOTKIT INSTALLED - REINSTALLING WINDOWS WON'T SAVE YOU",
        "🎯 GPS COORDINATES BROADCAST TO DARK WEB HITMAN FORUM (LOL JK... OR?)",
        "⚠️ INTERPOL, FBI, NSA, AND YOUR LOCAL CYBER POLICE NOTIFIED",
        "🔥 YOUR NUDES FOUND AND UPLOADED TO IMGUR (404 VIEWS ALREADY)",
        "💰 STEAM INVENTORY TRADED AWAY - GOODBYE CS:GO SKINS",
        "🎮 WORLD OF WARCRAFT ACCOUNT SOLD FOR $50 ON PLAYERAUCTIONS",
        "🏦 PAYPAL DRAINED, VENMO EMPTIED, ZELLE CLEANED OUT",
        "📱 ICLOUD BACKUP DOWNLOADED - ALL YOUR SELFIES ARE OURS",
        "⚡ MINING ETHEREUM ON YOUR GPU - ENJOY THE 100°C TEMPS"
    ]

    chosen_alerts = random.sample(alerts, 3)
    print("\033[41m\033[97m" + "█"*180 + "\033[0m")
    for alert in chosen_alerts:
        print("\033[41m\033[97m" + alert.center(180) + "\033[0m")
    print("\033[41m\033[97m" + "█"*180 + "\033[0m\n")

    # FAKE SYSTEM DESTRUCTION
    print("\033[91m" + "╔" + "═"*178 + "╗\033[0m")
    print("\033[91m║" + " 💣 SYSTEM SELF-DESTRUCT SEQUENCE INITIATED 💣 ".center(180) + "║\033[0m")
    print("\033[91m" + "╠" + "═"*178 + "╣\033[0m")
    print("\033[91m║ [CRITICAL] THIS WILL PERMANENTLY DESTROY:" + " "*118 + "║")
    print("\033[91m║    • ALL FILES ON ALL DRIVES (C:, D:, E:, EXTERNAL USB, NETWORK SHARES)" + " "*78 + "║")
    print("\033[91m║    • MASTER BOOT RECORD (MBR) AND GUID PARTITION TABLE (GPT)" + " "*88 + "║")
    print("\033[91m║    • UEFI/BIOS FIRMWARE (HARDWARE WILL BE BRICKED)" + " "*98 + "║")
    print("\033[91m║    • ALL RECOVERY PARTITIONS AND SYSTEM RESTORE POINTS" + " "*94 + "║")
    print("\033[91m║    • WINDOWS REGISTRY, USER PROFILES, AND SYSTEM FILES" + " "*94 + "║")
    print("\033[91m║ [WARNING] NO RECOVERY POSSIBLE - THIS IS PERMANENT - YOU ARE FINISHED" + " "*80 + "║")
    print("\033[91m" + "╚" + "═"*178 + "╝\033[0m\n")

    destruction_tasks = [
        "WIPING BOOT SECTOR",
        "CORRUPTING UEFI FIRMWARE",
        "DELETING SYSTEM32",
        "DESTROYING MBR",
        "OVERWRITING PARTITION TABLE",
        "CLEARING CMOS MEMORY",
        "BRICKING HARDWARE",
        "FORMATTING ALL DRIVES",
        "DELETING RECOVERY PARTITION",
        "ERASING BACKUP FILES"
    ]

    for sec in range(60, 0, -1):
        task = random.choice(destruction_tasks)
        progress = 100 - int((sec/60) * 100)
        bar = "█" * (progress // 2) + "░" * (50 - progress // 2)
        print(
            f"\r   ⏱️  T-MINUS {sec:02d} SECONDS TO TOTAL ANNIHILATION... [{bar}] {progress:>3}% │ {task:<30} │ (Ctrl+C won't save you)",
            end="",
            flush=True,
        )
        time.sleep(0.06)
    print("\n   \033[93m✓ SEQUENCE ABORTED AT LAST SECOND... YOU WERE 0.0001 SECONDS FROM OBLIVION.\033[0m\n")

    # RANSOM NOTE
    print("\033[41m\033[97m" + "╔" + "═"*178 + "╗\033[0m")
    print("\033[41m\033[97m║" + " YOUR FILES HAVE BEEN ENCRYPTED ".center(180) + "║\033[0m")
    print("\033[41m\033[97m" + "╠" + "═"*178 + "╣\033[0m")
    btc_address = f"1Grok{random.randint(10000,99999)}Evil{random.randint(1000,9999)}x"
    ransom_amount = random.randint(15, 50)
    print(
        f"\033[41m\033[97m║ All your files have been encrypted with military-grade AES-256 + RSA-4096."
        + " "*76 + "║\033[0m"
    )
    print(
        f"\033[41m\033[97m║ To get your files back, you must pay {ransom_amount} BTC (~${ransom_amount * random.randint(42000,68000):,} USD) to:"
        + " "*73 + "║\033[0m"
    )
    print(f"\033[41m\033[97m║   Bitcoin Address: {btc_address}" + " "*125 + "║\033[0m")
    print(f"\033[41m\033[97m║ After payment, contact: decrypt@onionmail.org with your unique ID: {session_id}" + " "*52 + "║\033[0m")
    print(f"\033[41m\033[97m║ You have 48 hours. After that, the price DOUBLES every 24 hours." + " "*83 + "║\033[0m")
    print(f"\033[41m\033[97m║ After 7 days, your decryption key will be destroyed FOREVER." + " "*88 + "║\033[0m")
    print(f"\033[41m\033[97m║ DO NOT try to decrypt files yourself - they will be corrupted permanently." + " "*74 + "║\033[0m")
    print(f"\033[41m\033[97m║ DO NOT contact police or FBI - we will know and delete everything." + " "*83 + "║\033[0m")
    print("\033[41m\033[97m" + "╚" + "═"*178 + "╝\033[0m\n")

    # BINARY/HEX CHAOS
    print("\033[90m" + "─"*180 + "\033[0m")
    for _ in range(8):
        line_type = random.choice(["binary", "hex"])
        if line_type == "binary":
            print("\033[90m" + "".join(random.choice("01") for _ in range(178)) + "\033[0m")
        else:
            print("\033[90m" + " ".join("".join(random.choices(hex_chars, k=2)) for _ in range(44)) + "\033[0m")
    print("\033[90m" + "─"*180 + "\033[0m")

    # GLITCH EFFECT
    glitch_messages = [
        "Y̴̨̛O̸͜͠U̴̧̕ ̷̀͜C̸͝͠A̴̡̕N̷̢̛N̶̨͘O̸̧͠T̵̢̛ ̸̀͜E̸̡̛S̷̨̛C̶̢̕A̸̢̛P̶̧͝E̷̡͘",
        "W̷̨͝E̷̢͘ ̸̨͝A̵̢͠R̷̨̛E̸̢͝ ̴̢̕E̷̡͠V̸̢͝Ę̵̕R̷̢͠Y̸̧̛W̵̡̕H̷̢͠Ę̸͝R̴̢̕Ę̷͝",
        "N̷̨̛O̵̢͝ ̸̢̕O̵̢͠N̷̨͝E̵̢̛ ̸̡̕C̷̨͠A̴̢͝N̸̢̛ ̷̡͝S̵̨͠A̸̢͠V̸̨͝E̵̢̛ ̷̡̕Y̸̢͠Ǫ̷̛U̵̧͝"
    ]

    print()
    glitch_text(random.choice(glitch_messages), 5)
    print()

    # FOOTER
    total_damage = (money//1000000) + random.randint(5,15)
    print(f"\033[90m{'═'*180}\033[0m")
    print(f"\033[90m[RUNTIME: {iteration * 12:.1f}s] [ITERATION: {iteration}] [TOTAL DAMAGE: ${total_damage}M+ USD] [FILES ENCRYPTED: {total_files_encrypted:,}]\033[0m")
    print(f"\033[90m[NEXT CYCLE IN 1s] [CTRL+C DISABLED] [TASK MANAGER BLOCKED] [NO ESCAPE] [YOU ARE TRAPPED]\033[0m")
    print(f"\033[90m{'═'*180}\033[0m")
    print("\033[91m")
    print("                                      ☠️  YOU HAVE BEEN PWNED BY GROK HACKING TEAM ☠️")
    print("\033[0m")

    time.sleep(1.0)

    # MALWARE ANALYSIS
    print("\033[96m" + "╔" + "═"*178 + "╗\033[0m")
    print("\033[96m║" + " 🦠 ACTIVE MALWARE PROCESSES & SYSTEM HOOKS 🦠 ".center(180) + "║\033[0m")
    print("\033[96m" + "╠" + "═"*178 + "╣\033[0m")
    print(f"║ {'PROCESS NAME':<35} │ {'PID':>8} │ {'MEM (MB)':>10} │ {'CPU %':>8} │ {'THREADS':>8} │ {'HANDLES':>8} │ {'STATUS':<30} ║")
    print("\033[96m" + "╠" + "═"*178 + "╣\033[0m")

    processes = [
        ("cryptominer-xmrig.exe", random.randint(4000,8000), random.randint(450,1200), random.randint(85,98), random.randint(12,24), random.randint(234,567)),
        ("keylogger-ultimate-v9.dll", random.randint(500,2000), random.randint(45,150), random.randint(5,15), random.randint(4,8), random.randint(89,234)),
        ("ransomware-ryuk-payload.bin", random.randint(8000,15000), random.randint(678,1567), random.randint(40,80), random.randint(16,32), random.randint(456,1234)),
        ("backdoor-cobalt-strike.exe", random.randint(1000,3000), random.randint(234,678), random.randint(10,25), random.randint(8,16), random.randint(178,456)),
        ("screenlogger-capture.sys", random.randint(2000,4000), random.randint(345,890), random.randint(15,35), random.randint(6,12), random.randint(123,345)),
        ("webcam-hijack-module.dll", random.randint(3000,6000), random.randint(456,1123), random.randint(20,45), random.randint(10,20), random.randint(234,678)),
        ("rootkit-necurs-kernel.sys", random.randint(1500,3500), random.randint(123,456), random.randint(8,18), random.randint(4,8), random.randint(67,189)),
        ("trojan-emotet-loader.exe", random.randint(2500,5500), random.randint(345,789), random.randint(12,28), random.randint(8,16), random.randint(145,389)),
        ("rat-njrat-controller.exe", random.randint(1800,4200), random.randint(267,634), random.randint(15,32), random.randint(6,14), random.randint(98,267)),
        ("stealer-redline-info.dll", random.randint(2200,4800), random.randint(189,523), random.randint(18,38), random.randint(8,18), random.randint(134,423))
    ]

    for proc, pid, mem, cpu, threads, handles in processes:
        status = random.choice([
            "\033[91m● STEALING DATA\033[96m",
            "\033[91m● ENCRYPTING\033[96m",
            "\033[91m● UPLOADING\033[96m",
            "\033[91m● MONITORING\033[96m"
        ])
        print(f"║ {proc:<35} │ {pid:>8} │ {mem:>10} │ {cpu:>8}% │ {threads:>8} │ {handles:>8} │ {status:<40} ║")

    print("\033[96m" + "╚" + "═"*178 + "╝\033[0m\n")

    # FILE ENCRYPTION PROGRESS
    print("\033[91m" + "╔" + "═"*178 + "╗\033[0m")
    print("\033[91m║" + " 🔒 GLOBAL FILE ENCRYPTION - AES-256-GCM + RSA-4096 + CHACHA20-POLY1305 🔒 ".center(180) + "║\033[0m")
    print("\033[91m" + "╠" + "═"*178 + "╣\033[0m")

    file_types = [
        '.doc','.docx','.pdf','.jpg','.png','.mp4','.avi','.zip','.rar','.sql','.db',
        '.mdb','.ppt','.pptx','.xls','.xlsx','.txt','.code','.json','.xml','.backup',
        '.vmdk','.vdi'
    ]
    folders = [
        'Documents','Pictures','Videos','Desktop','Downloads','Music','OneDrive','Dropbox',
        'Work','Projects','Backup','Database'
    ]
    extensions = [
        'locky','crypt','pwned','grok','locked','encrypted','corona','darkness','paycrypt','fucked'
    ]

    for i in range(101):
        bar = "█" * (i) + "░" * (100 - i)
        files = random.randint(7000, 158000)
        total_files_encrypted += random.randint(50, 200)
        current_folder = random.choice(folders)
        current_file = f"C:\\Users\\{user}\\{current_folder}\\{random.choice(['important','confidential','private','secret','backup','project'])}{random.randint(1,9999)}{random.choice(file_types)}"
        print(f"\r║ [{bar}] {i:>3}% │ {files:,} files │ Total: {total_files_encrypted:,} │ {current_file:<90} .{random.choice(extensions):<12} ║", end="")
        time.sleep(0.012)
    print("\n\033[91m" + "╚" + "═"*178 + "╝\033[0m\n")

    # DATABASE EXTRACTION
    print("\033[95m" + "╔" + "═"*178 + "╗\033[0m")
    print("\033[95m║" + " 📊 DATABASE EXTRACTION & CREDENTIAL HARVESTING 📊 ".center(180) + "║\033[0m")
    print("\033[95m" + "╠" + "═"*178 + "╣\033[0m")

    databases = [
        ("users_production.db", random.randint(24000,85000), "usernames, emails, password hashes (MD5/SHA1 cracked)", f"{random.randint(234,876)} MB"),
        ("financial_records.db", random.randint(8000,25000), "credit cards, bank accounts, SSN, routing numbers", f"{random.randint(456,1234)} MB"),
        ("customer_data.sql", random.randint(15000,67000), "PII, addresses, phone numbers, DOB, national IDs", f"{random.randint(678,2345)} MB"),
        ("sessions_active.db", random.randint(12000,45000), "auth tokens, JWT, cookies, API keys, OAuth tokens", f"{random.randint(345,987)} MB"),
        ("contacts_master.db", random.randint(5000,18000), "contact lists, relationships, social graphs", f"{random.randint(123,567)} MB"),
        ("payment_gateway.sql", random.randint(3000,12000), "transaction history, payment methods, billing info", f"{random.randint(234,789)} MB"),
        ("employee_hr.db", random.randint(1500,8000), "salary info, performance reviews, disciplinary records", f"{random.randint(89,345)} MB"),
        ("medical_records.db", random.randint(4000,15000), "health data, prescriptions, diagnoses, insurance", f"{random.randint(456,1234)} MB"),
        ("crypto_wallets.json", random.randint(200,2000), "private keys, seed phrases, wallet addresses", f"{random.randint(12,89)} MB"),
        ("backup_archives.tar.gz", random.randint(50000,250000), "complete system backups, configuration files", f"{random.randint(5678,15234)} MB")
    ]

    for db, records, content, size in databases:
        status_icon = random.choice([
            "\033[92m✓ EXTRACTED\033[95m",
            "\033[93m⟳ CRACKING\033[95m",
            "\033[91m↑ UPLOADING\033[95m",
        ])
        print(f"║ {status_icon} │ {db:<30} │ {records:>8,} records │ {size:>10} │ {content:<60} ║")
        time.sleep(0.05)

    print("\033[95m" + "╚" + "═"*178 + "╝\033[0m")
