function Contact (container_id, url) {
    var container = document.getElementById(container_id);

    var form;
    var apartment;
    var startDate;
    var endDate;

    function init() {

        form = document.createElement("form");
        form.action = url;
        form.method = "post";
        form.classList = "contact-wrapper";
        var table = document.createElement("table");
        table.classList = "form-table";
        table.innerHTML = "" +
            "<tbody>" +
                "<tr>" +
                    "<th>" +
                        "Jméno a příjmení" +
                    "</th>" +
                    "<td>" +
                        "<input type=\"text\" autocomplete=\"name\" name=\"calendar-name\">" +
                    "</td>" +
                "</tr>" +
                "<tr>" +
                    "<th>" +
                        "E-mail" +
                    "</th>" +
                    "<td>" +
                        "<input type=\"text\" autocomplete=\"email\" name=\"calendar-mail\">" +
                    "</td>" +
                "</tr>" +
                "<tr>" +
                    "<th>" +
                        "Apartmán" +
                    "</th>" +
                    "<td>" +
                        "<input type=\"text\" name=\"calendar-apartment\" readonly=\"readonly\">" +
                    "</td>" +
                "</tr>" +
                "<tr>" +
                    "<th>" +
                        "Datum od" +
                    "</th>" +
                    "<td>" +
                        "<input type=\"text\" name=\"calendar-start-date\" readonly=\"readonly\">" +
                    "</td>" +
                "</tr>" +
                "<tr>" +
                    "<th>" +
                        "Datum do" +
                    "</th>" +
                    "<td>" +
                        "<input type=\"text\" name=\"calendar-end-date\" readonly=\"readonly\">" +
                    "</td>" +
                "</tr>" +
                "<tr>" +
                    "<th>" +
                        "Poznámka" +
                    "</th>" +
                    "<td>" +

                        "<textarea type=\"text\" name=\"calendar-comment\" size=\"50\"></textarea>" +
                    "</td>" +
                "</tr>" +
            "</tbody>";
        apartment = table.querySelector('input[name="calendar-apartment"]');
        startDate = table.querySelector('input[name="calendar-start-date"]');
        endDate = table.querySelector('input[name="calendar-end-date"]');

        var action = document.createElement("input");
        action.setAttribute("type", "hidden");
        action.setAttribute("name", "action");
        action.setAttribute("value", "calendar_contact_form");

        var feedback = document.createElement("div");
        feedback.classList = "feedback";

        var submit = document.createElement("input");
        submit.setAttribute("type", "submit");
        submit.setAttribute("value", "Odeslat");

        form.appendChild(table);
        form.appendChild(action);
        form.appendChild(feedback);
        form.appendChild(submit);

        form.onsubmit = function(event) {
            event.preventDefault();
            var ajax = new XMLHttpRequest();
            ajax.onreadystatechange = function() {
                if (this.readyState == 4) {
                    if (this.status == 200) {
                        submit.setAttribute("value", "Děkujeme");
                    } else {
                        submit.setAttribute("value", "Něco se pokazilo...");
                    }
                    feedback.innerHTML = this.responseText;
                    feedback.style.display = "block";
                }
            }
            ajax.open("POST", url);
            ajax.send(new FormData(form));
        };

        container.style.position = 'relative';
        container.appendChild(form);
    }

    function setApartment(apartment_id) {
        apartment.value = apartment_id;
    }

    function setFromDate(date) {
        startDate.value = date;
    }

    function setToDate(date) {
        endDate.value = date;
        form.style.visibility = "visible";
        form.style.opacity = 1;
    }

    return {
        init: init,
        setApartment: setApartment,
        setFromDate: setFromDate,
        setToDate: setToDate
    }
}