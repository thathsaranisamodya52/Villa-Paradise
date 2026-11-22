function cancelBooking(rowId) {
    // පරිශීලකයාගෙන් අවලංගු කිරීමට කැමැත්ත විමසීම
    const confirmCancel = confirm("ඔබට මෙම වෙන්කිරීම (Booking) අවලංගු කිරීමට අවශ්‍ය බව ස්ථිරද?");
    
    if (confirmCancel) {
        // අදාළ Row එක සොයා ගැනීම
        const targetRow = document.getElementById(rowId);
        
        if (targetRow) {
            // Status Badge එක ඇති සෛලය සොයා එය වෙනස් කිරීම
            const statusCell = targetRow.querySelector('.status-badge');
            if (statusCell) {
                statusCell.className = 'status-badge status-cancelled';
                statusCell.textContent = 'Cancelled';
            }
            
            // Action බටන් එක ඇති සෛලය සොයා බටන් එක ඉවත් කිරීම
            const actionCell = targetRow.cells[6]; // 7 වන සෛලය (Index 6)
            if (actionCell) {
                actionCell.textContent = '-';
            }
            
            alert("ඔබගේ වෙන්කිරීම සාර්ථකව අවලංගු කරන ලදී.");
        }
    }
}
