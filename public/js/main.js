document.querySelectorAll('.delete').forEach((e) => [
    e.addEventListener('click', (event) => {
        event.preventDefault()
        if (confirm("Are you sure?")) {
            window.location.href = e.href;
        } else {
        }
    }),
])
