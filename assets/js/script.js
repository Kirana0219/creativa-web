// Orders Filter
flatpickr("#dateRange", {
    mode: "range",
    dateFormat: "M d, Y",

    onClose: function(selectedDates, dateStr, instance) {
        if (selectedDates.length === 2) {

            const start = selectedDates[0];
            const end = selectedDates[1];

            const startText = start.toLocaleDateString('en-US', {
                month: 'short',
                day: '2-digit'
            });

            const endText = end.toLocaleDateString('en-US', {
                month: 'short',
                day: '2-digit',
                year: 'numeric'
            });
            instance.input.value = `${startText} - ${endText}`;
        }
    }
});

document.querySelectorAll(".dropdown-item").forEach(item=>{
    item.addEventListener("click",function(){
        document.querySelector(".dropdown-toggle").innerHTML=this.innerText;
    });
});