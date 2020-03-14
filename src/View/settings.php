<section id="addEntry" class="">
    <h2>Settings</h2>
    <form action="/settings" method="post">
        <label for="emailInput">Change Email</label><br/>
        <input id="emailInput" placeholder="Email" type="email" name="email" value="<?=$user['email']?>"><br/>


        <label for="usernameInput">Change Username</label><br/>
        <input id="usernameInput" placeholder="Username" type="text" name="username" value="<?=$user['username']?>"><br/>

        <label for="passwordInput">Password</label><br/>
        <input type="password" id="passwordInput" placeholder="Password" name="password"/><br/>

        <label for="passwordRepeatInput">Password</label><br/>
        <input type="password" id="passwordRepeatInput" placeholder="Password" name="passwordRepeat"/><br/>

        <button>Submit</button>
    </form>
</section>
