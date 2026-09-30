<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wind Challenge</title>
    <style>
        * {
            padding: 0;
            margin: 0;
            cursor: none;
            user-select: none;
        }

        body {
            position: relative;
            overflow: hidden;
            background-color: #fff;
            width: 100vw;
            height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen,
                Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
        }

        /* Custom cursor dot */
        .cursor {
            position: fixed;
            z-index: 100;
            width: 17px;
            height: 23px;
            background-image: url("ASSETS/default.png");
            background-repeat: no-repeat;
            background-size: contain;
            will-change: transform;
            display: none;
            pointer-events: none;
            top: 0;
            left: 0;
        }

        /* Full-screen overlay before pointer lock */
        .pointer_lock_banner {
            position: fixed;
            z-index: 50;
            display: flex;
            flex-direction: column;
            gap: 12px;
            width: 100vw;
            height: 100vh;
            box-sizing: border-box;
            align-items: center;
            justify-content: center;
            border: 10px dashed #fff;
            background-color: rgba(0, 0, 0, 0.9);
            color: #fff;
            font-size: 3.5rem;
            font-weight: 700;
            text-align: center;
        }

        .pointer_lock_banner p {
            font-size: 1.2rem;
            font-weight: 400;
            opacity: 0.7;
        }

        /* Fan — right-anchored, full viewport height, stand clips off-screen bottom */
        .fan_block {
            position: fixed;
            top: 0;
            right: 0;
            width: 100vh; /* square image, so width = height = 100vh */
            height: 100vh;
            overflow: hidden;
        }

        .fan {
            position: absolute;
            top: 10vh;   /* nudge up slightly to show full grill, hide base */
            right: -5vh; /* push right to crop stand off-screen */
            height: 120vh;
            width: auto;
        }

        /* Wind: full height, runs from left edge to where fan grill starts */
        .wind {
            position: fixed;
            top: 50%;
            left: 0;
            transform: translateY(-50%);
            width: 72vw;
            height: 50vh;
            background-image: url("ASSETS/wind.png");
            background-repeat: repeat;
            background-size: 80px;
            will-change: background-position;
            z-index: 1;
        }

        .continue_btn {
            position: fixed;
            top: 50%;
            left: 50vw;
            transform: translateY(-50%);
            height: 49px;
            padding: 0 28px;
            background-color: #5856d6;
            border: none;
            border-radius: 10px;
            color: #fff;
            font-family: inherit;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(88, 86, 214, 0.4);
            z-index: 20;
        }

        /* Knob hitzone — visible glowing button */
        .fan_knob {
            position: fixed;
            bottom: 15.8vh;
            right: 12.4vw;
            width: 55px;
            height: 55px;
            border-radius: 50%;
            z-index: 30;
           
        }


  

        /* Wind OFF state */
        .wind.off {
            opacity: 0;
            pointer-events: none;
        }
    </style>
</head>
<body>

    <div class="pointer_lock_banner">
        Click to start
        <p>Try to click the Continue button — the fan won't make it easy.</p>
    </div>

    <div class="cursor"></div>

    <div class="fan_block">
        <img src="ASSETS/renefan2.png" alt="Fan" class="fan">
    </div>

    <div class="wind" id="wind"></div>

    <!-- Invisible knob hotzone -->
    <div class="fan_knob" id="fan-knob"></div>

    <!-- Button is outside fan_block so it sits at true viewport center -->
    <button class="continue_btn" id="continue-btn">Continue →</button>

    <script>
        const cursor = document.querySelector(".cursor");
        const wind   = document.getElementById("wind");
        const banner = document.querySelector(".pointer_lock_banner");
        const btn    = document.getElementById("continue-btn");
        const knob   = document.getElementById("fan-knob");

        let windOffsetX    = 0;
        let cursorX        = window.innerWidth / 2;
        let cursorY        = window.innerHeight / 2;
        let isCursorLocked = false;
        let windEnabled    = true;

        // Check if cursor is over knob hitzone
        const isOverKnob = () => {
            const r = knob.getBoundingClientRect();
            return cursorX >= r.left && cursorX <= r.right &&
                   cursorY >= r.top  && cursorY <= r.bottom;
        };

        // While locked, clicks toggle wind if cursor is on knob
        document.addEventListener("click", () => {
            if (isCursorLocked && isOverKnob()) {
                windEnabled = !windEnabled;
                wind.classList.toggle("off", !windEnabled);
                knob.classList.toggle("wind-off", !windEnabled);
            }
        });

        // Engage pointer lock on first click (only if not over knob)
        document.body.addEventListener("click", (e) => {
            if (!isCursorLocked) {
                document.body.requestPointerLock();
            }
        });

        // True when cursor is inside the wind zone
        const isInWind = () => {
            const r = wind.getBoundingClientRect();
            if (cursorX > r.right)  return false;
            if (cursorY < r.top || cursorY > r.bottom) return false;
            return true;
        };

        // Apply cursor movement with wind resistance
        const updateCursorPosition = (e) => {
            if (e.movementX > 0 && windEnabled && isInWind()) {
                cursorX += e.movementX / (1 + (cursorX / window.innerWidth) * 3);
            } else {
                cursorX += e.movementX;
            }
            cursorY += e.movementY;

            cursorX = Math.max(0, Math.min(cursorX, window.innerWidth));
            cursorY = Math.max(0, Math.min(cursorY, window.innerHeight));

            cursor.style.transform = `translateX(${cursorX}px) translateY(${cursorY}px)`;
        };

        // Win condition: cursor lands on button
        btn.addEventListener("click", () => {
            document.exitPointerLock();
            alert("🎉 You made it! The wind couldn't stop you.");
        });

        // Animate wind scroll + passive cursor drift
        let prevTime;
        const animationFrameStep = (currTime) => {
            const timeDelta = prevTime
                ? Math.min(20, Math.max(10, currTime - prevTime))
                : 10;
            prevTime = currTime;

            if (isCursorLocked) {
                if (windEnabled) {
                    windOffsetX -= 0.6 * timeDelta;
                    wind.style.backgroundPositionX = `${windOffsetX}px`;

                    if (isInWind()) {
                        cursorX -= 0.6 * timeDelta;
                        if (cursorX < 0) cursorX = 0;
                        cursor.style.transform = `translateX(${cursorX}px) translateY(${cursorY}px)`;
                    }
                }
            }

            window.requestAnimationFrame(animationFrameStep);
        };

        window.requestAnimationFrame(animationFrameStep);

        // Toggle pointer lock UI
        document.addEventListener("pointerlockchange", () => {
            if (document.pointerLockElement === null) {
                document.removeEventListener("mousemove", updateCursorPosition, false);
                cursor.style.display = "none";
                banner.style.display = "flex";
                isCursorLocked = false;
            } else {
                document.addEventListener("mousemove", updateCursorPosition, false);
                cursor.style.display = "block";
                banner.style.display = "none";
                isCursorLocked = true;
            }
        });
    </script>
</body>
</html>
