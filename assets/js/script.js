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

const orderRows = [...document.querySelectorAll(".orders-table .table-row")];
const pageInfo = document.querySelector(".page-info");
const pagination = document.querySelector("[data-pagination]");

if (pageInfo && pagination) {
    const perPage = Number(pageInfo.dataset.perPage) || 10;
    let currentPage = 1;

    const getTotalPages = () => Math.max(1, Math.ceil(orderRows.length / perPage));

    const getVisiblePages = (totalPages) => {
        if (totalPages <= 5) {
            return Array.from({ length: totalPages }, (_, index) => index + 1);
        }

        if (currentPage <= 3) {
            return [1, 2, 3, "...", totalPages];
        }

        if (currentPage >= totalPages - 2) {
            return [1, "...", totalPages - 2, totalPages - 1, totalPages];
        }

        return [1, "...", currentPage, "...", totalPages];
    };

    const showPage = (page) => {
        const totalPages = getTotalPages();
        currentPage = Math.min(Math.max(page, 1), totalPages);

        const start = (currentPage - 1) * perPage;
        const end = start + perPage;

        orderRows.forEach((row, index) => {
            row.hidden = index < start || index >= end;
        });

        if (!orderRows.length) {
            pageInfo.textContent = "Showing 0 orders";
            pagination.innerHTML = "";
            return;
        }

        pageInfo.textContent = `Showing ${start + 1} to ${Math.min(end, orderRows.length)} of ${orderRows.length} orders`;
        renderPagination(totalPages);
    };

    const createButton = (label, page, isActive = false, isDisabled = false) => {
        const button = document.createElement("button");
        button.type = "button";
        button.textContent = label;
        button.disabled = isDisabled;
        button.classList.toggle("page-number", Number.isInteger(Number(label)));
        button.classList.toggle("active", isActive);
        button.addEventListener("click", () => showPage(page));
        return button;
    };

    const renderPagination = (totalPages) => {
        pagination.innerHTML = "";
        pagination.append(createButton("Previous", currentPage - 1, false, currentPage === 1));

        getVisiblePages(totalPages).forEach((page) => {
            if (page === "...") {
                const dots = document.createElement("span");
                dots.textContent = "...";
                pagination.append(dots);
                return;
            }

            pagination.append(createButton(page, page, page === currentPage));
        });

        pagination.append(createButton("Next", currentPage + 1, false, currentPage === totalPages));
    };

    showPage(1);
}
