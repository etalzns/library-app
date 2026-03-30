<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Booking Rules - Library@West</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --navy: #1a375f;
            --light-gray: #f4f6f8;
            --white: #ffffff;
        }

        body {
            margin: 0;
            background-color: #ddd;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Phone frame setup */
        .phone-container {
            width: 360px;
            height: 740px;
            background-color: #f0f2f5;
            position: relative;
            overflow: hidden; /* Keeps the outer shell fixed */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
            border-radius: 35px;
            border: 8px solid #333; /* Phone border effect */
        }

        /* Content Card */
        .content-card {
            background: #f8f9fa;
            margin: 20px;
            border-radius: 25px;
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            max-height: calc(100% - 40px);
        }

        h2 {
            text-align: center;
            font-size: 20px;
            color: var(--navy);
            margin: 0 0 15px 0;
        }

        /* SCROLLABLE AREA */
        .rules-container {
            background: white;
            border-radius: 15px;
            padding: 15px;
            overflow-y: auto; /* This enables the scroll */
            font-size: 13.5px;
            line-height: 1.6;
            color: #444;
            border: 1px solid #eee;
            flex: 1; /* Takes up remaining space */
            min-height: 0; /* Critical for flex scroll to work */
            margin-bottom: 15px;
            scrollbar-width: thin;
            scrollbar-color: var(--navy) #f1f1f1;
        }

        /* Custom Scrollbar for Chrome/Safari */
        .rules-container::-webkit-scrollbar {
            width: 5px;
        }
        .rules-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        .rules-container::-webkit-scrollbar-thumb {
            background: var(--navy);
            border-radius: 10px;
        }

        .rules-container h4 {
            margin: 0 0 10px 0;
            text-transform: uppercase;
            font-size: 14px;
            color: var(--navy);
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }

        ol { padding-left: 20px; margin-bottom: 15px;}
        li { margin-bottom: 10px; }

        /* Agreement Section */
        .agreement {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 5px;
            font-size: 13px;
            font-weight: 600;
            color: #333;
            background: #eef2f7;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--navy);
            cursor: pointer;
        }

        /* Button Styling */
        .btn-proceed {
            background-color: #d1d8e0;
            color: #7f8c8d;
            border: none;
            padding: 14px;
            border-radius: 12px;
            width: 100%;
            font-weight: bold;
            font-size: 16px;
            cursor: not-allowed;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            display: block;
            pointer-events: none;
        }

        .btn-proceed.active {
            background-color: var(--navy);
            color: white;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(26, 55, 95, 0.3);
            pointer-events: auto;
        }

        .btn-proceed:active {
            transform: scale(0.98);
        }
    </style>
</head>
<body>

<div class="phone-container">
    <div class="content-card">
        <h2><i class="fas fa-book-reader"></i> Booking Rules</h2>

        <div class="rules-container">
            <h4>Room Rules:</h4>
            <ol>
                <li><strong>Capacity (Small Rooms):</strong> A1 to A7, 9 & 10 require <strong>2 to 4 pax</strong>.</li>
                <li><strong>Capacity (Large Rooms):</strong> A8, 11 & 13 require <strong>4 to 8 pax</strong>.</li>
                <li><strong>Usage:</strong> Rooms are strictly for discussions, presentations, and role play. Individual study must be done at external tables.</li>
                <li><strong>Prohibited:</strong> Strictly no food, drinks (except water), or sleeping allowed inside.</li>
                <li><strong>Lateness:</strong> Bookings are automatically cancelled if you are <strong>10 minutes late</strong>.</li>
                <li><strong>Cleanliness:</strong> Please clear all trash before leaving for the next user.</li>
                <li><strong>Misuse:</strong> Users found misusing the facility will be asked to vacate immediately.</li>
            </ol>

            <h4>Agreement & Privacy:</h4>
            <p>By proceeding, you consent to <strong>Library@West</strong> collecting your student data to manage room bookings and identifying users for safety and security purposes.</p>
        </div>

        <div class="agreement">
            <input type="checkbox" id="agreeCheckbox">
            <label for="agreeCheckbox">I agree to the terms above.</label>
        </div>

        <a href="book.php" id="proceedBtn" class="btn-proceed">Proceed to Booking</a>
    </div>
</div>

<script>
    const checkbox = document.getElementById('agreeCheckbox');
    const proceedBtn = document.getElementById('proceedBtn');

    checkbox.addEventListener('change', function() {
        if (this.checked) {
            proceedBtn.classList.add('active');
        } else {
            proceedBtn.classList.remove('active');
        }
    });
</script>

</body>
</html>