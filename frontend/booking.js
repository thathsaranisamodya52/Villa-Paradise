
const roomSelect = document.getElementById('room-select');
const checkInInput = document.getElementById('check-in');
const checkOutInput = document.getElementById('check-out');

const summaryRoom = document.getElementById('summary-room');
const summaryNights = document.getElementById('summary-nights');
const summaryTotal = document.getElementById('summary-total');


function calculateTotal() {
    const pricePerNight = parseFloat(roomSelect.value);
    const selectedOption = roomSelect.options[roomSelect.selectedIndex];
    const roomName = selectedOption.text === "කාමරයක් තෝරන්න..." ? "-" : selectedOption.getAttribute('data-name');
    
    const checkInDate = new Date(checkInInput.value);
    const checkOutDate = new Date(checkOutInput.value);

    
    if (pricePerNight && checkInInput.value && checkOutInput.value) {
        
        const timeDifference = checkOutDate.getTime() - checkInDate.getTime();
        const totalNights = Math.ceil(timeDifference / (1000 * 3600 * 24));

        if (totalNights > 0) {
            const totalPrice = totalNights * pricePerNight;
            
            
            summaryRoom.textContent = roomName;
            summaryNights.textContent = totalNights;
            summaryTotal.textContent = `$${totalPrice}`;
        } else {
            
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


roomSelect.addEventListener('change', calculateTotal);
checkInInput.addEventListener('change', calculateTotal);
checkOutInput.addEventListener('change', calculateTotal);
