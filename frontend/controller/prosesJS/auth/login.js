function login() {
    document.getElementById("form1").addEventListener("submit", (e) => {
        e.preventDefault();

        let formData = new FormData();

        formData.append("username", document.querySelector("input[name='username']").value);
        formData.append("password", document.querySelector("input[name='password']").value);

        $.ajax({
            url: "controller/push/login",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(data){
                console.log(data);
                Swal.fire({
                    icon: data.icon,
                    title: data.title,
                    text: data.text
                }).then(() => {
                    window.location.href = data.redirect;
                });
            }
        });
    });
}

login();