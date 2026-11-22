// HTML elements ලබා ගැනීම
const roomSelect = document.getElementById('room-select');
const checkInInput = document.getElementById('check-in');
const checkOutInput = document.getElementById('check-out');

const summaryRoom = document.getElementById('summary-room');
const summaryNights = document.getElementById('summary-nights');
const summaryTotal = document.getElementById('summary-total');

// ගණනය කිරීම සිදු කරන ශ්‍රිතය (Function)
function calculateTotal() {
    const pricePerNight = parseFloat(roomSelect.value);
    const selectedOption = roomSelect.options[roomSelect.selectedIndex];
    const roomName = selectedOption.text === "කාමරයක් තෝරන්න..." ? "-" : selectedOption.getAttribute('data-name');
    
    const checkInDate = new Date(checkInInput.value);
    const checkOutDate = new Date(checkOutInput.value);

    // කාමරය සහ දින නිවැරදිව ඇතුළත් කර ඇත්දැයි බැලීම
    if (pricePerNight && checkInInput.value && checkOutInput.value) {
        // දින දෙක අතර වෙනස මිලිසෙකන්ඩ් වලින් ලබාගෙන දින ගණන සෑදීම
        const timeDifference = checkOutDate.getTime() - checkInDate.getTime();
        const totalNights = Math.ceil(timeDifference / (1000 * 3600 * 24));

        if (totalNights > 0) {
            const totalPrice = totalNights * pricePerNight;
            
            // පෝරමයේ විස්තර යාවත්කාලීන කිරීම (Updating View)
            summaryRoom.textContent = roomName;
            summaryNights.textContent = totalNights;
            summaryTotal.textContent = `$${totalPrice}`;
        } else {
            // වැරදි දින ඇතුළත් කර ඇත්නම් (Check-out එක පැමිණෙන දිනට පෙර නම්)
            summaryRoom.textContent = roomName;
            summaryNights.textContent = "Invalid Dates";
            summaryTotal.textContent = "$0";
        }
    } else {
        summaryRoom.textContent = roomName;
        summaryNights.textContent = "0";
        summaryTotal.textContent = "$0";
    }
}

// Input අගයන් වෙනස් වන විට ඉබේම ක්‍රියාත්මක වීමට Event Listeners එකතු කිරීම
roomSelect.addEventListener('change', calculateTotal);
checkInInput.addEventListener('change', calculateTotal);
checkOutInput.addEventListener('change', calculateTotal);
