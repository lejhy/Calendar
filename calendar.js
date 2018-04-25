function Calendar(data, container_id, contact, apartment_ids) {

    if (apartment_ids === undefined) {
        apartment_ids = [];
        for (apartment in data.apartments) {
            apartment_ids.push(data.apartments[apartment].id);
        }
    }

    var container = document.getElementById(container_id);
    var months = ["Leden", "Únor", "Březen", "Duben", "Květen", "Červen", "Červenec", "Srpen", "Září", "Říjen", "Listopad", "Prosinec"];
    var days = ["Pondělí", "Úterý", "Středa", "Čtvrtek", "Pátek", "Sobota", "Neděle"];
    var today = new Date();
    var perWeekStart = new Date(data.per_week_start);
    var perWeekStartDay = 6; // Saturday value
    var perWeekEnd = new Date(data.per_week_end);
    var perWeekEndDay = 5; // Friday value
    var currentMonth = today.getMonth();
    var currentYear = today.getFullYear();
    var currentDate = today.getDate();

    var calendar;
    var header;
    var yearContainer;
    var monthContainer;
    var datesContainer;
    var noteContainer;

    var selectedApartment_id;
    var selectedFromDate;
    var selectedToDate;
    var maximumToDate;

    function init() {
        calendar = document.createElement("div");
        calendar.classList += "calendar-wrapper";

        var previous = document.createElement("div");
        previous.classList = "calendar-previous";
        previous.onclick = showPreviousMonth;

        var next = document.createElement("div");
        next.classList = "calendar-next";
        next.onclick = showNextMonth;

        var daysContainer = document.createElement("div");
        daysContainer.classList = "calendar-days";
        for (var day in days) {
            daysContainer.innerHTML += "<div class='calendar-day'>" + days[day].substring(0, 2) + "</div>";
        }

        header = document.createElement("div");
        header.classList = "header";
        yearContainer = document.createElement("div");
        yearContainer.classList = "calendar-year";
        monthContainer = document.createElement("div");
        monthContainer.classList = "calendar-month";
        datesContainer = document.createElement("div");
        datesContainer.classList = "calendar-dates";

        noteContainer = document.createElement("div");
        noteContainer.classList = "calendar-note";
        noteContainer.innerHTML = "Rezervace pouze na celý týden od "+perWeekStart.getDate()+". "+(perWeekStart.getMonth()+1)+". do "+perWeekEnd.getDate()+". "+(perWeekEnd.getMonth()+1);

        calendar.appendChild(previous);
        header.appendChild(yearContainer);
        header.appendChild(monthContainer);
        calendar.appendChild(header);
        calendar.appendChild(daysContainer);
        calendar.appendChild(datesContainer);
        calendar.appendChild(noteContainer);
        calendar.appendChild(next);

        container.prepend(calendar);

        render();
    }

    function render() {
        var firstDateOfMonth = new Date(currentYear, currentMonth, 1);
        var firstDate = new Date(currentYear, currentMonth, 2-firstDateOfMonth.getDay());
        var lastDateOfMonth = new Date(currentYear, currentMonth + 1, 0);
        var lastDate = new Date(currentYear, currentMonth+1, 7-lastDateOfMonth.getDay());
        var currentDate = new Date(firstDate);
        yearContainer.innerHTML = currentYear;
        monthContainer.innerHTML = months[currentMonth];
        datesContainer.innerHTML = "";
        do {
            var exclude = currentDate < selectedFromDate || currentDate > maximumToDate;
            var dateContainer = createDateContainer(new Date(currentDate), exclude);
            if (currentDate < firstDateOfMonth || currentDate > lastDateOfMonth)
                dateContainer.classList += " different-month";
            if (currentDate <= selectedFromDate && currentDate >= selectedFromDate || currentDate <= selectedToDate && currentDate >= selectedFromDate)
                dateContainer.classList += " selected";
            datesContainer.appendChild(dateContainer);
            currentDate.setDate(currentDate.getDate() + 1);
        } while (currentDate <= lastDate);
    }

    function createDateContainer(date, exclude) {
        var dateContainer = document.createElement("div");
        dateContainer.classList = 'calendar-date';
        dateContainer.innerHTML = date.getDate();
        if (exclude === undefined || exclude === false) {
            var dateInfo = document.createElement("div");
            dateInfo.classList = "date-info";
            var available = false;
            for (var apartment_idIndex in apartment_ids) {
                var apartment_id = apartment_ids[apartment_idIndex];
                if (selectedApartment_id === undefined || selectedApartment_id === apartment_id) {
                    var apartmentInfo = createApartmentInfo(date, apartment_id);
                    if (apartmentInfo !== undefined) {
                        dateInfo.appendChild(apartmentInfo);
                        available = true;
                    }
                }
            }
            dateContainer.appendChild(dateInfo);

            if (available) {
                dateContainer.classList += ' available';
                dateContainer.onclick = function() {
                    selectDate(date);
                }
            }
            else dateContainer.classList += ' unavailable';
        } else {
            dateContainer.classList += ' unavailable';
        }

        return dateContainer;
    }

    function createApartmentInfo(date, apartment_id) {
        var reserved = isReserved(apartment_id, date);
        if (!reserved) {
            var priceValue = getPrice(apartment_id, date);
            if (priceValue > 0) {

                var apartmentInfo = document.createElement("div");
                apartmentInfo.classList = "apartment-info";
                apartmentInfo.onclick = function (e) {
                    selectApartment(date, apartment_id);
                    e.stopPropagation();
                };

                var apartment = document.createElement("div");
                apartment.classList = "apartment";
                apartment.innerHTML += getApartment(apartment_id);
                apartmentInfo.appendChild(apartment);

                var price = document.createElement("div");
                price.classList = "price";
                price.innerHTML = priceValue+".00,-";

                apartmentInfo.appendChild(price);

                return apartmentInfo;
            }
        }
    }

    function selectApartment(date, apartment_id) {
        if (selectedApartment_id === undefined) {
            selectedApartment_id = apartment_id;
            contact.setApartment(data.apartments.find(function(e) {
                return e.id === apartment_id;
            }).apartment);
        }
        if (date >= perWeekStart && date <= perWeekEnd) {
            selectWeek(date);
        } else {
            selectDate(date);
        }
    }

    function selectWeek(date) {
        var firstWeekDay = new Date(date.getFullYear(), date.getMonth(), date.getDate());
        while (firstWeekDay.getDay() !== perWeekStartDay) {
            firstWeekDay.setDate(firstWeekDay.getDate() - 1);
            if (isReserved(selectedApartment_id, firstWeekDay) || getPrice(selectedApartment_id, firstWeekDay) === 0) {
                firstWeekDay.setDate(firstWeekDay.getDate() + 1);
                break;
            }
        }
        var lastWeekDay = new Date(date.getFullYear(), date.getMonth(), date.getDate());
        while (lastWeekDay.getDay() !== perWeekEndDay) {
            lastWeekDay.setDate(lastWeekDay.getDate() + 1);
            if (isReserved(selectedApartment_id, lastWeekDay) || getPrice(selectedApartment_id, lastWeekDay) === 0) {
                lastWeekDay.setDate(lastWeekDay.getDate() - 1);
                break;
            }
        }

        selectedFromDate = firstWeekDay;
        contact.setFromDate(firstWeekDay.getDate()+". "+months[firstWeekDay.getMonth()]+" "+firstWeekDay.getFullYear());
        selectedToDate = lastWeekDay;
        maximumToDate = selectedToDate;
        contact.setToDate(lastWeekDay.getDate()+". "+months[lastWeekDay.getMonth()]+" "+lastWeekDay.getFullYear());
        render();
    }

    function selectDate(date) {
        if (selectedApartment_id !== undefined) {
            if (selectedFromDate === undefined) {
                selectedFromDate = date;
                contact.setFromDate(date.getDate()+". "+months[date.getMonth()]+" "+date.getFullYear());

                maximumToDate = new Date(date);
                var maximumLengthOfStay = 28;
                for (var i = 0; i < maximumLengthOfStay; i++) {
                    maximumToDate.setDate(maximumToDate.getDate() + 1);
                    if (isReserved(selectedApartment_id, maximumToDate)) break;
                    if (getPrice(selectedApartment_id, maximumToDate) === 0) break;
                    if (maximumToDate >= perWeekStart && maximumToDate <= perWeekEnd) break;
                }
                maximumToDate.setDate(maximumToDate.getDate() - 1); // decrement to get the last valid date

                render();
            } else {
                selectedToDate = date;
                contact.setToDate(date.getDate()+". "+months[date.getMonth()]+" "+date.getFullYear());
                render();
            }
        }
    }

    function getPrice(apartment_id, date) {
        var prices = data.prices;
        var latestPrice = 0;
        for (price in prices) {
            if (prices[price].apartment_id === apartment_id) {
                var start = new Date(prices[price].start_date);
                if (start <= date) {
                    var end = new Date(prices[price].end_date);
                    if (end >= date) {
                        latestPrice = prices[price].price;
                    }
                }
            }
        }
        return latestPrice;
    }

    function isReserved(apartment_id, date) {
        var reservations = data.reservations;
        for (reservation in reservations) {
            if (reservations[reservation].apartment_id === apartment_id) {
                var start = new Date(reservations[reservation].start_date);
                if (start <= date) {
                    var end = new Date(reservations[reservation].end_date);
                    if (end >= date) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    function getApartment(apartment_id) {
        for (apartment in data.apartments) {
            if (data.apartments[apartment].id === apartment_id) {
                return data.apartments[apartment].apartment;
            }
        }
    }

    function showPreviousMonth() {
        currentMonth--;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        currentDate = new Date(currentYear, currentMonth + 1, 0).getDate();
        render();
    }

    function showNextMonth() {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        currentDate = 1;
        render();
    }

    return {
        init: init
    };
}
