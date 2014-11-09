<html>
    <head>
        <title>Error</title>
    </head>
    <body style="background-color:whitesmoke;">
        <div style="line-height:100px;background-color:pink; text-align:center; width:300px; height:100px; position:absolute; top:0; bottom:0; left:0; right:0; margin:auto;">
            Sorry, something messed up :)
            <br />
            <?php
                echo $e->getmessage();
            ?>
        </div>
    </body>
</html>
