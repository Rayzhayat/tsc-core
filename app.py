import tkinter as tk
from tkinter import ttk, messagebox
import time
import random
import json
import os
from difflib import SequenceMatcher
from datetime import datetime

# ===============================================
# IMPROVED VERSION - FITUR BARU TANPA RUSAK YANG LAMA
# ===============================================

CODE_SNIPPETS = {
    'python': {
        'easy': [
            'x = 10',
            'print("Hello World")',
            'name = "Python"',
            'for i in range(5):',
            'if x > 0:',
            'def hello():',
            'list = [1, 2, 3]',
            'import math',
            'result = 5 + 3',
            'age = 25',
            'is_valid = True',
            'colors = ["red", "blue"]',
        ],
        'medium': [
            'def calculate(a, b):\n    return a + b',
            'numbers = [i**2 for i in range(10)]',
            'with open("file.txt", "r") as f:\n    data = f.read()',
            'try:\n    result = 10 / 0\nexcept ZeroDivisionError:\n    print("Error")',
            'class Person:\n    def __init__(self, name):\n        self.name = name',
            'lambda x: x * 2',
            'dict_data = {"key": "value", "number": 42}',
            'students = [s for s in students if s.grade > 80]',
            'def factorial(n):\n    return 1 if n <= 1 else n * factorial(n-1)',
        ],
        'hard': [
            'def fibonacci(n):\n    if n <= 1:\n        return n\n    return fibonacci(n-1) + fibonacci(n-2)',
            'import pandas as pd\ndf = pd.read_csv("data.csv")\nprint(df.head())',
            '@decorator\ndef wrapper(*args, **kwargs):\n    result = func(*args, **kwargs)\n    return result',
            'numbers = [x for x in range(100) if x % 2 == 0 and x % 3 == 0]',
            'async def fetch_data(url):\n    async with aiohttp.ClientSession() as session:\n        async with session.get(url) as response:\n            return await response.json()',
        ]
    },
    'php': {
        'easy': [
            '$x = 10;',
            'echo "Hello World";',
            '$name = "PHP";',
            'for ($i = 0; $i < 5; $i++) {',
            'if ($x > 0) {',
            'function hello() {',
            '$array = [1, 2, 3];',
            '$result = $a + $b;',
            '$users = [];',
        ],
        'medium': [
            'function calculate($a, $b) {\n    return $a + $b;\n}',
            '$numbers = array_map(fn($x) => $x * 2, $arr);',
            'try {\n    $result = 10 / 0;\n} catch (Exception $e) {\n    echo $e->getMessage();\n}',
            '$filtered = array_filter($users, fn($u) => $u->age > 18);',
        ],
        'hard': [
            'public function store(Request $request) {\n    $validated = $request->validate([\n        "title" => "required|max:255",\n    ]);\n    return response()->json($validated);\n}',
            'Route::middleware("auth")->group(function() {\n    Route::get("/dashboard", [DashboardController::class, "index"]);\n});',
        ]
    },
    'javascript': {
        'easy': [
            'let x = 10;',
            'console.log("Hello World");',
            'const name = "JavaScript";',
            'for (let i = 0; i < 5; i++) {',
            'if (x > 0) {',
            'const sum = a + b;',
            'let isActive = true;',
        ],
        'medium': [
            'function calculate(a, b) {\n    return a + b;\n}',
            'const numbers = arr.map(x => x ** 2);',
            'fetch("api.json").then(res => res.json());',
            'const users = data.filter(u => u.age > 18);',
            'setTimeout(() => console.log("Done"), 1000);',
        ],
        'hard': [
            'async function fetchData(url) {\n    const response = await fetch(url);\n    const data = await response.json();\n    return data;\n}',
            'const debounce = (fn, delay) => {\n    let timeout;\n    return (...args) => {\n        clearTimeout(timeout);\n        timeout = setTimeout(() => fn(...args), delay);\n    };\n};',
        ]
    },
    'java': {
        'easy': [
            'int x = 10;',
            'System.out.println("Hello World");',
            'String name = "Java";',
            'boolean isValid = true;',
            'double price = 99.99;',
        ],
        'medium': [
            'public int calculate(int a, int b) {\n    return a + b;\n}',
            'List<String> names = new ArrayList<>();',
            'for (String name : names) {\n    System.out.println(name);\n}',
        ],
        'hard': [
            '@Override\npublic void onCreate(Bundle savedInstanceState) {\n    super.onCreate(savedInstanceState);\n    setContentView(R.layout.activity_main);\n}',
            'public class User implements Serializable {\n    private String name;\n    public User(String name) {\n        this.name = name;\n    }\n}',
        ]
    },
    'text_id': {
        'easy': [
            'selamat pagi apa kabar',
            'hari ini cuaca cerah sekali',
            'saya suka belajar pemrograman',
            'coding itu menyenangkan',
            'mari kita belajar bersama',
            'teknologi sangat membantu',
            'semangat untuk hari ini',
        ],
        'medium': [
            'teknologi berkembang sangat pesat dalam beberapa tahun terakhir dan artificial intelligence menjadi topik yang sangat populer',
            'kunci sukses dalam belajar programming adalah konsistensi dan latihan terus menerus',
            'indonesia memiliki potensi besar dalam industri teknologi digital dengan banyaknya talenta muda yang berbakat',
        ],
        'hard': [
            'revolusi digital telah mengubah cara kita hidup bekerja dan berkomunikasi transformasi ini tidak hanya mempengaruhi sektor bisnis tetapi juga pendidikan kesehatan dan berbagai aspek kehidupan lainnya',
            'kecerdasan buatan atau artificial intelligence merupakan cabang ilmu komputer yang berfokus pada pengembangan sistem yang dapat melakukan tugas yang biasanya memerlukan kecerdasan manusia seperti pengenalan pola pengambilan keputusan dan pemahaman bahasa alami',
        ]
    },
    'text_en': {
        'easy': [
            'good morning how are you',
            'the weather is beautiful today',
            'i love learning to code',
            'practice makes perfect',
            'never stop learning',
        ],
        'medium': [
            'technology has evolved rapidly over the past few years and artificial intelligence has become hot topics among developers worldwide',
            'consistent practice and dedication are essential keys to mastering programming skills',
        ],
        'hard': [
            'the digital revolution has transformed the way we live work and communicate this transformation affects not only the business sector but also education healthcare and various other aspects of life',
            'artificial intelligence and machine learning are reshaping industries by enabling computers to learn from data identify patterns and make decisions with minimal human intervention',
        ]
    }
}

class StatsManager:
    """Kelola statistik user"""
    def __init__(self):
        self.stats_file = "typing_stats.json"
        self.stats = self.load_stats()
    
    def load_stats(self):
        if os.path.exists(self.stats_file):
            try:
                with open(self.stats_file, 'r') as f:
                    return json.load(f)
            except:
                return self.default_stats()
        return self.default_stats()
    
    def default_stats(self):
        return {
            "total_sessions": 0,
            "best_wpm": 0,
            "best_accuracy": 0,
            "total_chars": 0,
            "history": []
        }
    
    def save_stats(self):
        with open(self.stats_file, 'w') as f:
            json.dump(self.stats, f, indent=2)
    
    def add_session(self, wpm, accuracy, chars, time_taken, lang, diff):
        self.stats["total_sessions"] += 1
        self.stats["total_chars"] += chars
        self.stats["best_wpm"] = max(self.stats["best_wpm"], wpm)
        self.stats["best_accuracy"] = max(self.stats["best_accuracy"], accuracy)
        
        session = {
            "date": datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
            "wpm": wpm,
            "accuracy": round(accuracy, 1),
            "chars": chars,
            "time": round(time_taken, 2),
            "language": lang,
            "difficulty": diff
        }
        
        self.stats["history"].append(session)
        if len(self.stats["history"]) > 50:  # Keep last 50
            self.stats["history"] = self.stats["history"][-50:]
        
        self.save_stats()

class TypingTrainerGUI:
    def __init__(self, root):
        self.root = root
        self.root.title("Code & Text Typing Trainer Pro")
        self.root.geometry("1100x800")
        
        # Theme colors
        self.themes = {
            'dark': {
                'bg': '#1e1e1e',
                'fg': '#c9d1d9',
                'accent': '#61dafb',
                'code_bg': '#0d1117',
                'header_bg': '#2d2d2d',
                'button_bg': '#4CAF50',
            },
            'light': {
                'bg': '#ffffff',
                'fg': '#333333',
                'accent': '#0066cc',
                'code_bg': '#f6f8fa',
                'header_bg': '#e8e8e8',
                'button_bg': '#28a745',
            },
            'dracula': {
                'bg': '#282a36',
                'fg': '#f8f8f2',
                'accent': '#ff79c6',
                'code_bg': '#1e1f29',
                'header_bg': '#44475a',
                'button_bg': '#50fa7b',
            }
        }
        
        self.current_theme = 'dark'
        self.theme = self.themes[self.current_theme]
        self.root.configure(bg=self.theme['bg'])

        self.current_code = ""
        self.start_time = None
        self.is_started = False
        self.selected_language = tk.StringVar(value="python")
        self.selected_difficulty = tk.StringVar(value="easy")
        
        # Stats manager
        self.stats_manager = StatsManager()
        
        # Mistake tracking
        self.mistake_count = 0
        self.total_keystrokes = 0

        self.setup_gui()
        self.load_new_code()

    def change_theme(self, theme_name):
        self.current_theme = theme_name
        self.theme = self.themes[theme_name]
        self.apply_theme()
    
    def apply_theme(self):
        """Apply theme ke semua widgets"""
        self.root.configure(bg=self.theme['bg'])
        
        # Update all frames
        for widget in [self.header_frame, self.control_frame, self.lang_frame, 
                      self.diff_frame, self.code_area, self.input_area, self.stats_frame]:
            try:
                widget.configure(bg=self.theme['bg'] if widget != self.header_frame and widget != self.stats_frame else self.theme['header_bg'])
            except:
                pass
        
        # Update labels
        for widget in [self.title_label, self.instruction_label, self.input_label,
                      self.timer_label, self.wpm_label, self.accuracy_label, self.mistakes_label]:
            try:
                widget.configure(bg=self.theme['header_bg'] if widget in [self.title_label, self.timer_label, self.wpm_label, self.accuracy_label, self.mistakes_label] else self.theme['bg'],
                               fg=self.theme['accent'] if widget in [self.title_label, self.instruction_label, self.input_label] else self.theme['fg'])
            except:
                pass
        
        # Update text widgets
        self.code_display.configure(bg=self.theme['code_bg'], fg=self.theme['fg'])
        self.input_text.configure(bg=self.theme['code_bg'], fg=self.theme['fg'])

    def setup_gui(self):
        # Header
        self.header_frame = tk.Frame(self.root, bg=self.theme['header_bg'], pady=15)
        self.header_frame.pack(fill='x')
        self.title_label = tk.Label(self.header_frame, text="⌨️ CODE & TEXT TYPING TRAINER PRO", 
                                    font=('Arial', 22, 'bold'),
                                    bg=self.theme['header_bg'], fg=self.theme['accent'])
        self.title_label.pack()

        # Control Panel
        self.control_frame = tk.Frame(self.root, bg=self.theme['bg'], pady=10)
        self.control_frame.pack(fill='x', padx=20)

        # Language Selection
        self.lang_frame = tk.Frame(self.control_frame, bg=self.theme['bg'])
        self.lang_frame.pack(side='left')
        tk.Label(self.lang_frame, text="Mode:", bg=self.theme['bg'], fg=self.theme['fg'], 
                font=('Arial', 11, 'bold')).pack(anchor='w')
        
        languages = [("🐍 Python", "python"), ("🐘 PHP", "php"), ("⚡ JS", "javascript"), 
                    ("☕ Java", "java"), ("🇮🇩 ID", "text_id"), ("🇬🇧 EN", "text_en")]
        for text, value in languages:
            tk.Radiobutton(self.lang_frame, text=text, variable=self.selected_language, value=value,
                          bg=self.theme['bg'], fg=self.theme['fg'], selectcolor=self.theme['header_bg'], 
                          command=self.load_new_code, font=('Arial', 10)).pack(side='left', padx=5)

        # Difficulty Selection
        self.diff_frame = tk.Frame(self.control_frame, bg=self.theme['bg'])
        self.diff_frame.pack(side='left', padx=30)
        tk.Label(self.diff_frame, text="Level:", bg=self.theme['bg'], fg=self.theme['fg'], 
                font=('Arial', 11, 'bold')).pack(anchor='w')
        
        difficulties = [("🟢 Easy", "easy"), ("🟡 Medium", "medium"), ("🔴 Hard", "hard")]
        for text, value in difficulties:
            tk.Radiobutton(self.diff_frame, text=text, variable=self.selected_difficulty, value=value,
                          bg=self.theme['bg'], fg=self.theme['fg'], selectcolor=self.theme['header_bg'],
                          command=self.load_new_code, font=('Arial', 10)).pack(side='left', padx=10)

        # Buttons
        button_frame = tk.Frame(self.control_frame, bg=self.theme['bg'])
        button_frame.pack(side='right')
        
        tk.Button(button_frame, text="🔄 Kode Baru", command=self.load_new_code,
                 bg=self.theme['button_bg'], fg='white', font=('Arial', 10, 'bold'), 
                 relief='flat', padx=15, pady=5, cursor='hand2').pack(side='left', padx=5)
        
        tk.Button(button_frame, text="📊 Stats", command=self.show_stats,
                 bg='#2196F3', fg='white', font=('Arial', 10, 'bold'), 
                 relief='flat', padx=15, pady=5, cursor='hand2').pack(side='left', padx=5)
        
        tk.Button(button_frame, text="🎨 Theme", command=self.show_theme_menu,
                 bg='#9C27B0', fg='white', font=('Arial', 10, 'bold'), 
                 relief='flat', padx=15, pady=5, cursor='hand2').pack(side='left', padx=5)

        # Code Display Area
        self.code_area = tk.Frame(self.root, bg=self.theme['bg'])
        self.code_area.pack(fill='both', expand=True, padx=30, pady=10)
        
        self.instruction_label = tk.Label(self.code_area, text="📝 Ketik kode ini:", 
                                         bg=self.theme['bg'], fg=self.theme['accent'], 
                                         font=('Arial', 14, 'bold'), anchor='w')
        self.instruction_label.pack(anchor='w')
        
        # Scrollbar untuk code display
        code_frame = tk.Frame(self.code_area, bg=self.theme['bg'])
        code_frame.pack(fill='both', expand=True)
        
        code_scrollbar = tk.Scrollbar(code_frame)
        code_scrollbar.pack(side='right', fill='y')
        
        self.code_display = tk.Text(code_frame, height=8, font=('Consolas', 15), 
                                   bg=self.theme['code_bg'], fg=self.theme['fg'],
                                   relief='flat', padx=20, pady=20, wrap='word', 
                                   state='disabled', yscrollcommand=code_scrollbar.set)
        self.code_display.pack(fill='both', expand=True)
        code_scrollbar.config(command=self.code_display.yview)

        # Input Area
        self.input_area = tk.Frame(self.root, bg=self.theme['bg'])
        self.input_area.pack(fill='both', expand=True, padx=30, pady=10)
        
        self.input_label = tk.Label(self.input_area, 
                                    text="⌨️ KETIK DI SINI (Ctrl+Enter: submit | Esc: reset)", 
                                    bg=self.theme['bg'], fg=self.theme['accent'], 
                                    font=('Arial', 14, 'bold'), anchor='w')
        self.input_label.pack(anchor='w')
        
        input_frame = tk.Frame(self.input_area, bg=self.theme['bg'])
        input_frame.pack(fill='both', expand=True)
        
        input_scrollbar = tk.Scrollbar(input_frame)
        input_scrollbar.pack(side='right', fill='y')
        
        self.input_text = tk.Text(input_frame, height=8, font=('Consolas', 15), 
                                 bg=self.theme['code_bg'], fg=self.theme['fg'],
                                 relief='flat', padx=20, pady=20, insertbackground=self.theme['accent'], 
                                 wrap='word', yscrollcommand=input_scrollbar.set)
        self.input_text.pack(fill='both', expand=True)
        input_scrollbar.config(command=self.input_text.yview)
        
        self.input_text.focus_set()

        # Bindings
        self.input_text.bind('<KeyPress>', self.on_key_press)
        self.input_text.bind('<KeyRelease>', self.check_typing)
        self.input_text.bind('<Control-Return>', lambda e: self.submit_code())
        self.input_text.bind('<Escape>', lambda e: self.load_new_code())

        # Stats Display
        self.stats_frame = tk.Frame(self.root, bg=self.theme['header_bg'], pady=12)
        self.stats_frame.pack(fill='x', padx=30, pady=(0, 10))
        
        self.timer_label = tk.Label(self.stats_frame, text="⏱️ Waktu: 0.00s", 
                                    bg=self.theme['header_bg'], fg=self.theme['fg'], 
                                    font=('Arial', 13, 'bold'))
        self.timer_label.pack(side='left', padx=30)
        
        self.wpm_label = tk.Label(self.stats_frame, text="🚀 WPM: 0", 
                                 bg=self.theme['header_bg'], fg=self.theme['fg'], 
                                 font=('Arial', 13, 'bold'))
        self.wpm_label.pack(side='left', padx=30)
        
        self.accuracy_label = tk.Label(self.stats_frame, text="🎯 Akurasi: 0%", 
                                      bg=self.theme['header_bg'], fg=self.theme['fg'], 
                                      font=('Arial', 13, 'bold'))
        self.accuracy_label.pack(side='left', padx=30)
        
        self.mistakes_label = tk.Label(self.stats_frame, text="❌ Kesalahan: 0", 
                                      bg=self.theme['header_bg'], fg=self.theme['fg'], 
                                      font=('Arial', 13, 'bold'))
        self.mistakes_label.pack(side='left', padx=30)

    def show_theme_menu(self):
        """Menu pilih theme"""
        theme_window = tk.Toplevel(self.root)
        theme_window.title("Pilih Theme")
        theme_window.geometry("350x250")
        theme_window.configure(bg=self.theme['bg'])
        theme_window.transient(self.root)
        theme_window.grab_set()
        
        tk.Label(theme_window, text="🎨 Pilih Theme", 
                font=('Arial', 16, 'bold'), bg=self.theme['bg'], 
                fg=self.theme['accent']).pack(pady=20)
        
        themes_list = [
            ("🌑 Dark Mode", "dark"),
            ("☀️ Light Mode", "light"),
            ("🧛 Dracula", "dracula")
        ]
        
        for text, theme in themes_list:
            btn = tk.Button(theme_window, text=text, 
                          command=lambda t=theme: [self.change_theme(t), theme_window.destroy()],
                          bg=self.theme['button_bg'], fg='white', 
                          font=('Arial', 12, 'bold'), relief='flat', 
                          padx=20, pady=10, cursor='hand2', width=20)
            btn.pack(pady=10)

    def show_stats(self):
        """Tampilkan window statistik"""
        stats_window = tk.Toplevel(self.root)
        stats_window.title("Statistik Latihan")
        stats_window.geometry("700x600")
        stats_window.configure(bg=self.theme['bg'])
        stats_window.transient(self.root)
        
        # Header
        tk.Label(stats_window, text="📊 STATISTIK LATIHAN KAMU", 
                font=('Arial', 18, 'bold'), bg=self.theme['bg'], 
                fg=self.theme['accent']).pack(pady=20)
        
        # Overall stats
        stats = self.stats_manager.stats
        overall_frame = tk.Frame(stats_window, bg=self.theme['header_bg'], relief='solid', bd=1)
        overall_frame.pack(fill='x', padx=30, pady=10)
        
        stats_text = f"""
        🎯 Total Sesi: {stats['total_sessions']}
        🏆 Best WPM: {stats['best_wpm']}
        ⭐ Best Accuracy: {stats['best_accuracy']:.1f}%
        ⌨️ Total Karakter: {stats['total_chars']:,}
        """
        
        tk.Label(overall_frame, text=stats_text, font=('Consolas', 13), 
                bg=self.theme['header_bg'], fg=self.theme['fg'], 
                justify='left').pack(pady=15, padx=20)
        
        # History
        tk.Label(stats_window, text="📝 Riwayat Terakhir:", 
                font=('Arial', 14, 'bold'), bg=self.theme['bg'], 
                fg=self.theme['accent']).pack(anchor='w', padx=30, pady=(10, 5))
        
        # Scrollable history
        history_frame = tk.Frame(stats_window, bg=self.theme['bg'])
        history_frame.pack(fill='both', expand=True, padx=30, pady=10)
        
        scrollbar = tk.Scrollbar(history_frame)
        scrollbar.pack(side='right', fill='y')
        
        history_text = tk.Text(history_frame, font=('Consolas', 11), 
                              bg=self.theme['code_bg'], fg=self.theme['fg'],
                              wrap='word', yscrollcommand=scrollbar.set)
        history_text.pack(fill='both', expand=True)
        scrollbar.config(command=history_text.yview)
        
        if stats['history']:
            for i, session in enumerate(reversed(stats['history'][-20:]), 1):
                entry = f"{i}. {session['date']} | {session['language'].upper()} ({session['difficulty']}) | WPM: {session['wpm']} | Akurasi: {session['accuracy']}% | {session['time']}s\n"
                history_text.insert('end', entry)
        else:
            history_text.insert('end', "Belum ada riwayat latihan. Ayo mulai latihan!")
        
        history_text.config(state='disabled')
        
        tk.Button(stats_window, text="Tutup", command=stats_window.destroy,
                 bg=self.theme['button_bg'], fg='white', font=('Arial', 11, 'bold'),
                 relief='flat', padx=30, pady=8, cursor='hand2').pack(pady=20)

    def load_new_code(self):
        """Load kode/text baru"""
        lang = self.selected_language.get()
        diff = self.selected_difficulty.get()
        self.current_code = random.choice(CODE_SNIPPETS[lang][diff])
        
        # Update instruction
        self.instruction_label.config(
            text="📝 Ketik teks ini:" if lang.startswith('text_') else "💻 Ketik kode ini:"
        )

        # Display code
        self.code_display.config(state='normal')
        self.code_display.delete('1.0', 'end')
        self.code_display.insert('1.0', self.current_code)
        self.code_display.config(state='disabled')

        self.reset_typing()

    def on_key_press(self, event):
        """Mulai timer saat user mulai ngetik"""
        if not self.is_started:
            self.is_started = True
            self.start_time = time.time()
            self.mistake_count = 0
            self.total_keystrokes = 0
            self.update_timer()
        
        # Track keystrokes (excluding control keys)
        if event.char and event.keysym not in ['Control_L', 'Control_R', 'Shift_L', 'Shift_R', 
                                                'Alt_L', 'Alt_R', 'Return', 'BackSpace']:
            self.total_keystrokes += 1

    def update_timer(self):
        """Update timer tiap 100ms"""
        if self.is_started:
            elapsed = time.time() - self.start_time
            self.timer_label.config(text=f"⏱️ Waktu: {elapsed:.2f}s")
            self.root.after(100, self.update_timer)

    def check_typing(self, event=None):
        """Realtime check accuracy dan WPM"""
        if not self.is_started:
            return
        
        typed = self.input_text.get('1.0', 'end-1c')
        elapsed = time.time() - self.start_time
        
        # Calculate WPM (5 chars = 1 word)
        words = len(typed) / 5
        minutes = elapsed / 60
        wpm = int(words / minutes) if minutes > 0 else 0
        
        # Calculate accuracy using SequenceMatcher
        accuracy = SequenceMatcher(None, self.current_code, typed).ratio() * 100
        
        # Count mistakes (simplified)
        mistakes = 0
        for i, char in enumerate(typed):
            if i < len(self.current_code):
                if char != self.current_code[i]:
                    mistakes += 1
        
        self.mistake_count = mistakes

        # Update display
        self.wpm_label.config(text=f"🚀 WPM: {wpm}")
        self.accuracy_label.config(text=f"🎯 Akurasi: {accuracy:.1f}%")
        self.mistakes_label.config(text=f"❌ Kesalahan: {self.mistake_count}")
        
        # Visual feedback - highlight jika akurasi rendah
        if accuracy < 70:
            self.input_text.config(bg='#3d1f1f')  # Dark red
        elif accuracy < 90:
            self.input_text.config(bg='#3d3d1f')  # Dark yellow
        else:
            self.input_text.config(bg=self.theme['code_bg'])

    def submit_code(self):
        """Submit dan show hasil"""
        if not self.is_started:
            return
        
        self.is_started = False

        typed = self.input_text.get('1.0', 'end-1c').rstrip()
        elapsed = time.time() - self.start_time
        
        # Calculate final stats
        accuracy = SequenceMatcher(None, self.current_code, typed).ratio() * 100
        wpm = int((len(typed)/5) / (elapsed/60)) if elapsed > 0 else 0
        
        # Save to stats
        self.stats_manager.add_session(
            wpm, accuracy, len(typed), elapsed,
            self.selected_language.get(),
            self.selected_difficulty.get()
        )
        
        # Rating system
        if accuracy >= 98 and wpm >= 80:
            rating = "🏆 LEGENDARY!"
            color = "#FFD700"
        elif accuracy >= 95 and wpm >= 70:
            rating = "⭐ MASTER!"
            color = "#4CAF50"
        elif accuracy >= 90 and wpm >= 50:
            rating = "👍 GOOD JOB!"
            color = "#2196F3"
        elif accuracy >= 80:
            rating = "📈 GETTING BETTER!"
            color = "#FF9800"
        else:
            rating = "💪 KEEP PRACTICING!"
            color = "#f44336"

        # Show result popup
        popup = tk.Toplevel(self.root)
        popup.title("Hasil Latihan")
        popup.geometry("550x450")
        popup.configure(bg=self.theme['bg'])
        popup.transient(self.root)
        popup.grab_set()
        
        # Rating dengan warna
        rating_label = tk.Label(popup, text=rating, font=('Arial', 24, 'bold'), 
                               bg=self.theme['bg'], fg=color)
        rating_label.pack(pady=20)
        
        # Stats
        result_text = f"""
⏱️ Waktu: {elapsed:.2f} detik
⌨️ Karakter: {len(typed)}
🎯 Akurasi: {accuracy:.1f}%
🚀 WPM: {wpm}
❌ Kesalahan: {self.mistake_count}
        """
        
        stats_label = tk.Label(popup, text=result_text, font=('Consolas', 16), 
                              bg=self.theme['bg'], fg=self.theme['fg'], justify='left')
        stats_label.pack(pady=20)
        
        # Personal best?
        if wpm == self.stats_manager.stats['best_wpm'] and wpm > 0:
            tk.Label(popup, text="🎉 NEW PERSONAL BEST WPM!", 
                    font=('Arial', 14, 'bold'), bg=self.theme['bg'], 
                    fg='#FFD700').pack()
        
        # Buttons
        btn_frame = tk.Frame(popup, bg=self.theme['bg'])
        btn_frame.pack(pady=20)
        
        tk.Button(btn_frame, text="🔄 Lanjut Latihan", 
                 command=lambda: [popup.destroy(), self.load_new_code()],
                 bg=self.theme['button_bg'], fg='white', font=('Arial', 12, 'bold'),
                 relief='flat', padx=20, pady=10, cursor='hand2').pack(side='left', padx=10)
        
        tk.Button(btn_frame, text="📊 Lihat Stats", 
                 command=lambda: [popup.destroy(), self.show_stats()],
                 bg='#2196F3', fg='white', font=('Arial', 12, 'bold'),
                 relief='flat', padx=20, pady=10, cursor='hand2').pack(side='left', padx=10)

    def reset_typing(self):
        """Reset semua state"""
        self.input_text.delete('1.0', 'end')
        self.input_text.config(bg=self.theme['code_bg'])
        self.is_started = False
        self.start_time = None
        self.mistake_count = 0
        self.total_keystrokes = 0
        
        self.timer_label.config(text="⏱️ Waktu: 0.00s")
        self.wpm_label.config(text="🚀 WPM: 0")
        self.accuracy_label.config(text="🎯 Akurasi: 0%")
        self.mistakes_label.config(text="❌ Kesalahan: 0")
    
        self.input_text.focus_set()

def main():
    root = tk.Tk()
    app = TypingTrainerGUI(root)
    root.mainloop()

if __name__ == "__main__":
    main()