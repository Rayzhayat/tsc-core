import turtle
import random
import time

# Setup screen
screen = turtle.Screen()
screen.bgcolor("black")
screen.title("Robot in Love - With Drawing Process ❤️🤖")
screen.setup(width=800, height=600)
# Tracer dimatiin dulu biar proses gambar keliatan
screen.tracer(0)  # Nanti kita nyalain lagi pas perlu cepet, tapi sekarang kita kontrol manual

# Turtle utama
drawer = turtle.Turtle()
drawer.hideturtle()
drawer.speed(8)  # Sedang, biar proses gambarnya keliatan enak (bukan instan)

# Turtle efek
effect = turtle.Turtle()
effect.hideturtle()
effect.speed(0)

# Fungsi gambar lengkap robot + heart (dengan proses)
def draw_robot_love(pulse_size, eye_color, shake=0):
    drawer.clear()  # Hapus frame sebelumnya
    
    # Badan robot
    drawer.penup()
    drawer.goto(0 + shake, -100)
    drawer.pendown()
    drawer.color("silver")
    drawer.begin_fill()
    for _ in range(4):
        drawer.forward(120)
        drawer.right(90)
    drawer.end_fill()
    
    # Kepala
    drawer.penup()
    drawer.goto(-50 + shake, 20)
    drawer.pendown()
    drawer.color("gray")
    drawer.begin_fill()
    for _ in range(4):
        drawer.forward(100)
        drawer.right(90)
    drawer.end_fill()
    
    # Mata glowing
    drawer.penup()
    drawer.goto(-25 + shake, 60)
    drawer.pendown()
    drawer.color(eye_color)
    drawer.begin_fill()
    drawer.circle(15)
    drawer.end_fill()
    
    drawer.penup()
    drawer.goto(25 + shake, 60)
    drawer.pendown()
    drawer.begin_fill()
    drawer.circle(15)
    drawer.end_fill()
    
    # Antena
    drawer.penup()
    drawer.goto(0 + shake, 120)
    drawer.pendown()
    drawer.color("red")
    drawer.width(6)
    drawer.goto(0 + shake, 160)
    drawer.dot(20, "orange")
    
    # Tangan
    drawer.color("silver")
    drawer.width(12)
    drawer.penup()
    drawer.goto(-60 + shake, 0)
    drawer.pendown()
    drawer.goto(-100 + shake, -20)
    drawer.penup()
    drawer.goto(60 + shake, 0)
    drawer.pendown()
    drawer.goto(100 + shake, -20)
    
    # Heart pulsing dengan proses curve yang keliatan
    drawer.penup()
    drawer.goto(0 + shake, -pulse_size // 3)
    drawer.pendown()
    drawer.color("crimson")
    drawer.begin_fill()
    drawer.fillcolor("deeppink" if pulse_size < 160 else "red")
    drawer.left(140)
    drawer.forward(pulse_size * 1.1)
    # Curve kiri (proses keliatan)
    for _ in range(200):
        drawer.right(1)
        drawer.forward(pulse_size * 0.01)
    drawer.left(120)
    # Curve kanan
    for _ in range(200):
        drawer.right(1)
        drawer.forward(pulse_size * 0.01)
    drawer.forward(pulse_size * 1.1)
    drawer.end_fill()
    drawer.setheading(0)
    drawer.width(1)

# Efek sparkles (dibikin pelan juga)
def draw_effects():
    effect.clear()
    effect.speed(6)  # Biar sparkles munculnya juga pelan
    for _ in range(10):
        x = random.randint(-200, 200)
        y = random.randint(-200, 200)
        size = random.randint(10, 18)
        effect.penup()
        effect.goto(x, y)
        effect.pendown()
        effect.color(random.choice(["pink", "white", "gold", "lightblue"]))
        effect.write("✨", font=("Arial", size, "bold"))
    effect.speed(0)

# Tulisan sekali aja
title = turtle.Turtle()
title.hideturtle()
title.penup()
title.goto(0, -260)
title.color("lightcyan")
title.write("LOVE MODE ACTIVATED ❤️🤖", align="center", font=("Courier", 20, "bold"))

# Animasi looping dengan proses gambar
pulse_size = 150
growing = True
eye_on = True
blink_timer = 0
shake = 0
direction = 1

while True:
    # Update pulsing
    if growing:
        pulse_size += 1
        if pulse_size >= 170:
            growing = False
    else:
        pulse_size -= 1
        if pulse_size <= 140:
            growing = True
    
    # Mata kedip pelan
    blink_timer += 1
    if blink_timer > 40:
        eye_on = not eye_on
        blink_timer = 0
    eye_color = "cyan" if eye_on else "darkblue"
    
    # Getar halus
    shake += direction * 0.5
    if abs(shake) > 10:
        direction *= -1
    
    # Gambar semuanya dengan proses
    draw_robot_love(pulse_size, eye_color, shake)
    draw_effects()
    
    screen.update()  # Update sekali per frame
    time.sleep(0.15)  # Delay agak lama biar proses gambarnya nikmat diliat