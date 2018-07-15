$("input#reg_password").on("focus keyup", function () {
        var score = 0;
        var a = $(this).val();
        var desc = new Array();
 
        // strength desc
        desc[0] = "Too short";
        desc[1] = "Weak";
        desc[2] = "Fair";
        desc[3] = "Good";
        desc[4] = "Strong";
 
        $("#pwd_strength_wrap").fadeIn(400);
         
        // password length
        if (a.length >= 6) {
            $("#length").removeClass("invalid").addClass("valid");
            score++;
        } else {
            $("#length").removeClass("valid").addClass("invalid");
        }
 
        // at least 1 digit in password
        if (a.match(/\d/)) {
            $("#pnum").removeClass("invalid").addClass("valid");
            score++;
        } else {
            $("#pnum").removeClass("valid").addClass("invalid");
        }
 
        // at least 1 capital & lower letter in password
        if (a.match(/[A-Z]/) && a.match(/[a-z]/)) {
            $("#capital").removeClass("invalid").addClass("valid");
            score++;
        } else {
            $("#capital").removeClass("valid").addClass("invalid");
        }
 
        // at least 1 special character in password {
        if ( a.match(/.[!,@,#,$,%,^,&,*,?,_,~,-,(,)]/) ) {
                $("#spchar").removeClass("invalid").addClass("valid");
                score++;
        } else {
                $("#spchar").removeClass("valid").addClass("invalid");
        }
 
 
        if(a.length > 0) {
                //show strength text
                $("#passwordDescription").text('Password Strength: ' + ' ' + desc[score]);
                // show indicator
                $("#passwordStrength").removeClass().addClass("strength"+score);
        } else {
                $("#passwordDescription").text('Password Strength: ');
                $("#passwordStrength").removeClass().addClass("strength"+score);
        }
});
 
$("input#reg_password").blur(function () {
        $("#pwd_strength_wrap").fadeOut(400);
});