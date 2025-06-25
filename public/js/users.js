const searchBar = document.querySelector(".sidebar .search input");
const searchBtn = document.querySelector(".sidebar .search button");
const usersList = document.querySelector(".sidebar .users-list");

// Toggle search bar and button active state
searchBtn.onclick = () => {
    searchBar.classList.toggle("active");
    searchBar.focus();
    searchBtn.classList.toggle("active");
    searchBar.value = "";
};

// Debounced search handler
let debounceTimeout;
searchBar.onkeyup = () => {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        let searchTerm = searchBar.value;
        if (searchTerm !== "") {
            searchBar.classList.add("active");
        } else {
            searchBar.classList.remove("active");
            // Reset to default user list if search is cleared
            let xhrReset = new XMLHttpRequest();
            xhrReset.open("GET", "../php/users.php", true);
            xhrReset.onload = () => {
                if (xhrReset.readyState === XMLHttpRequest.DONE && xhrReset.status === 200) {
                    usersList.innerHTML = xhrReset.response;
                }
            };
            xhrReset.send();
            return;
        }

        let xhr = new XMLHttpRequest();
        xhr.open("POST", "../php/search.php", true);
        xhr.onload = () => {
            if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                const newContent = xhr.response;
                if (usersList.innerHTML !== newContent) {
                    usersList.innerHTML = newContent;
                }
            } else {
                usersList.innerHTML = '<p>Error loading users.</p>';
            }
        };
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.send("searchTerm=" + encodeURIComponent(searchTerm));
    }, 300); // 300ms debounce for typing
};

// Optimized periodic polling
let lastContent = '';
setInterval(() => {
    let xhr = new XMLHttpRequest();
    xhr.open("GET", "../php/users.php", true);
    xhr.onload = () => {
        if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
            if (!searchBar.classList.contains("active")) {
                const newContent = xhr.response;
                if (lastContent !== newContent) {
                    usersList.innerHTML = newContent;
                    lastContent = newContent;
                }
            }
        }
    };
    xhr.send();
}, 5000);