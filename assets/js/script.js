document.addEventListener("DOMContentLoaded", () => {
    // ===== Date Picker =====
    const dateInput = document.querySelector("#dateRange");
    const dateFilter = document.querySelector(".date-filter");

    let startDate = null;
    let endDate = null;

    if (dateInput && typeof flatpickr === "function") {
        const calendar = flatpickr(dateInput, {
            mode: "range",
            dateFormat: "M d, Y",
            disableMobile: true,
            clickOpens: true,

            onClose(selectedDates, dateStr, instance) {
                if (selectedDates.length !== 2) {
                    startDate = null;
                    endDate = null;
                    showPage(1);
                    return;
                }

                startDate = new Date(selectedDates[0]);
                endDate = new Date(selectedDates[1]);

                startDate.setHours(0, 0, 0, 0);
                endDate.setHours(23, 59, 59, 999);

                const startText = startDate.toLocaleDateString("en-US", {
                    month: "short",
                    day: "2-digit"
                });

                const endText = endDate.toLocaleDateString("en-US", {
                    month: "short",
                    day: "2-digit",
                    year: "numeric"
                });

                instance.input.value = `${startText} - ${endText}`;

                showPage(1);
            }
        });

        dateFilter?.addEventListener("click", () => calendar.open());
    }

    // ===== Elements =====
    const orderRows = [...document.querySelectorAll(".orders-table .table-row")];
    const searchInput = document.querySelector(".search-order input");
    const pageInfo = document.querySelector(".page-info");
    const pagination = document.querySelector("[data-pagination]");

    if (!pageInfo || !pagination) return;

    const perPage = Number(pageInfo.dataset.perPage) || 5;

    let currentPage = 1;
    let currentStatus = "all";
    let currentSearch = "";

    // ===== Status Filter =====
    document.querySelectorAll(".dropdown-item").forEach(item => {
        item.addEventListener("click", e => {
            e.preventDefault();

            currentStatus = item.textContent.trim().toLowerCase();

            item.closest(".dropdown")
                .querySelector(".dropdown-toggle")
                .textContent = `Status: ${item.textContent.trim()}`;

            showPage(1);
        });
    });

    // ===== Search =====
    searchInput?.addEventListener("input", function () {
        currentSearch = this.value.trim().toLowerCase();
        showPage(1);
    });

    // ===== Filter =====
    const getFilteredRows = () => {
        return orderRows.filter(row => {
            const statusMatch =
                currentStatus === "all" ||
                row.dataset.status === currentStatus;

            const searchMatch =
                !currentSearch ||
                row.textContent.toLowerCase().includes(currentSearch);

            let dateMatch = true;

            if (startDate && endDate) {
                const orderDate = new Date(row.dataset.date);
                orderDate.setHours(12, 0, 0, 0);

                dateMatch =
                    orderDate >= startDate &&
                    orderDate <= endDate;
            }

            return statusMatch && searchMatch && dateMatch;
        });
    };

    // ===== Pagination =====
    const getVisiblePages = totalPages => {
        if (totalPages <= 5)
            return Array.from({ length: totalPages }, (_, i) => i + 1);

        if (currentPage <= 3)
            return [1, 2, 3, "...", totalPages];

        if (currentPage >= totalPages - 2)
            return [1, "...", totalPages - 2, totalPages - 1, totalPages];

        return [1, "...", currentPage, "...", totalPages];
    };

    const createButton = (label, page, active = false, disabled = false) => {
        const button = document.createElement("button");

        button.type = "button";
        button.textContent = label;
        button.disabled = disabled;

        button.classList.toggle("page-number", Number.isInteger(Number(label)));
        button.classList.toggle("active", active);

        button.addEventListener("click", () => showPage(page));

        return button;
    };

    const renderPagination = totalPages => {
        pagination.innerHTML = "";

        pagination.append(
            createButton(
                "Previous",
                currentPage - 1,
                false,
                currentPage === 1
            )
        );

        getVisiblePages(totalPages).forEach(page => {
            if (page === "...") {
                const dots = document.createElement("span");
                dots.textContent = "...";
                pagination.append(dots);
                return;
            }

            pagination.append(
                createButton(
                    page,
                    page,
                    page === currentPage
                )
            );
        });

        pagination.append(
            createButton(
                "Next",
                currentPage + 1,
                false,
                currentPage === totalPages
            )
        );
    };

    // ===== Render Table =====
    const showPage = page => {
        const filteredRows = getFilteredRows();

        const totalPages = Math.max(
            1,
            Math.ceil(filteredRows.length / perPage)
        );

        currentPage = Math.min(
            Math.max(page, 1),
            totalPages
        );

        const start = (currentPage - 1) * perPage;
        const end = start + perPage;

        orderRows.forEach(row => row.hidden = true);

        filteredRows.forEach((row, index) => {
            row.hidden = !(index >= start && index < end);
        });

        if (!filteredRows.length) {
            pageInfo.textContent = "Showing 0 orders";
            pagination.innerHTML = "";
            return;
        }

        pageInfo.textContent =
            `Showing ${start + 1} to ${Math.min(end, filteredRows.length)} of ${filteredRows.length} orders`;

        renderPagination(totalPages);
    };

    showPage(1);
});