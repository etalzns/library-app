const rooms = [
  { name: 'Room 10', cap: '4 pax', top: '45%', left: '37%', w: '8%', h: '12%' },
  { name: 'Room 11', cap: '8 pax', top: '45%', left: '29%', w: '8%', h: '12%' }
];

// Add a separation offset dynamically
const separation = 2; // in percentage

rooms.forEach(room => {
  const roomElement = document.createElement('div');
  roomElement.innerText = room.name;
  roomElement.style.position = 'absolute';
  roomElement.style.width = room.w;
  roomElement.style.height = room.h;
  roomElement.style.top = room.top;

  // Apply separation only between Room 10 and Room 11
  if (room.name === 'Room 10') {
    roomElement.style.left = `calc(${room.left} + ${separation}%)`;
  } else if (room.name === 'Room 11') {
    roomElement.style.left = `calc(${room.left} - ${separation}%)`;
  } else {
    roomElement.style.left = room.left;
  }

  document.body.appendChild(roomElement);
});
document.getElementById('fullSizeQR').onclick = function() {
    if (!currentBookingId) return;

    if(confirm("Check in now?")) {
        fetch('checkin.php?booking_id=' + currentBookingId)
        .then(res => res.text())
        .then(res => {

            if(res.trim() === "success"){
                alert("✅ Checked in!");
                location.reload();
            } else {
                alert(res); // shows error like expired / too early
            }

        });
    }
}
document.getElementById('bookingDetailsForm').onsubmit = function(e) {
        const input = document.getElementById('pax_input');
        const val = parseInt(input.value);
        const min = parseInt(input.getAttribute('min'));
        const max = parseInt(input.getAttribute('max'));

        if (val < min || val > max) {
            alert("This room requires a minimum of " + min + " and a maximum of " + max + " participants.");
            e.preventDefault();
            return false;
        }
    };

    const checkbox = document.getElementById('agreeCheckbox');
    const proceedBtn = document.getElementById('proceedBtn');

    checkbox.addEventListener('change', function() {
        if (this.checked) {
            proceedBtn.classList.add('active');
        } else {
            proceedBtn.classList.remove('active');
        }
    });
    function openQR(url) {
    document.getElementById('fullSizeQR').src = url;
    document.getElementById('qrModal').style.display = 'flex';
}
function closeQR() {
    document.getElementById('qrModal').style.display = 'none';
}
function confirmCancel(id) {
    if (confirm("Are you sure you want to cancel this booking?")) {
        window.location.href = "delete_booking.php?id=" + id;
    }
}
window.onclick = function(event) {
    if (event.target == document.getElementById('qrModal')) closeQR();
}
// Replace your old script with this
let currentBookingId = null;

function openQR(url, id) {
    currentBookingId = id; // Store the ID
    document.getElementById('fullSizeQR').src = url;
    document.getElementById('qrModal').style.display = 'flex';
}

// Add this: This "clicks" the QR to check in
document.getElementById('fullSizeQR').onclick = function() {
    if(confirm("Check in to this room now?")) {
        fetch('checkin.php?booking_id=' + currentBookingId)
            .then(r => r.text())
            .then(data => {
                alert("Checked in! Room is now RED on the map.");
                location.reload(); // Refresh to show "Checked In" status
            });
    }
    }
document.addEventListener("DOMContentLoaded", () => {
    document.getElementById('fullSizeQR').onclick = function() {
        if(confirm("Check in to this room now?")) {
            fetch('checkin.php?booking_id=' + currentBookingId)
                .then(r => r.text())
                .then(data => {
                    alert("Checked in! Room is now RED on the map.");
                    location.reload();
                });
        }
    };
});