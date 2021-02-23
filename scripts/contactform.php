<?php
session_start();
$mode = 'input';
$errmessage = array();
if( isset($_POST['back']) && $_POST['back'] ){
	// 何もしない
} else if( isset($_POST['confirm']) && $_POST['confirm'] ){
	// 確認画面
	if( !$_POST['fullname'] ) {
		$errmessage[] = "名前を入力してください";
	} else if( mb_strlen($_POST['fullname']) > 100 ){
		$errmessage[] = "名前は100文字以内にしてください";
	}
	$_SESSION['fullname']	= htmlspecialchars($_POST['fullname'], ENT_QUOTES);
	
	if( !$_POST['furigananame'] ) {
		$errmessage[] = "フリガナを入力してください";
	} else if( mb_strlen($_POST['furigananame']) > 100 ){
		$errmessage[] = "フリガナは100文字以内にしてください";
	}
	$_SESSION['furigananame']	= htmlspecialchars($_POST['furigananame'], ENT_QUOTES);
	
	$_SESSION['companyname']	= htmlspecialchars($_POST['companyname'], ENT_QUOTES);

	if( !$_POST['phone'] ) {
		$errmessage[] = "電話番号を入力してください";
	} else if( mb_strlen($_POST['phone']) > 200 ){
		$errmessage[] = "電話番号は200文字以内にしてください";
	}
	$_SESSION['phone']	= htmlspecialchars($_POST['phone'], ENT_QUOTES);

	if( !$_POST['email'] ) {
		$errmessage[] = "Eメールを入力してください";
	} else if( mb_strlen($_POST['email']) > 200 ){
		$errmessage[] = "Eメールは200文字以内にしてください";
	} else if( !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) ){
		$errmessage[] = "メールアドレスが不正です";
	}
	$_SESSION['email']	= htmlspecialchars($_POST['email'], ENT_QUOTES);

	if( !$_POST['message'] ){
		$errmessage[] = "お問い合わせ内容を入力してください";
	} else if( mb_strlen($_POST['message']) > 500 ){
		$errmessage[] = "お問い合わせ内容は500文字以内にしてください";
	}
	$_SESSION['message'] = htmlspecialchars($_POST['message'], ENT_QUOTES);

	if( $errmessage ){
		$mode = 'input';
	} else {
	  $token = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM)); // php5のとき
	  //$token = bin2hex(random_bytes(32));                                   // php7以降
	  $_SESSION['token']  = $token;
		$mode = 'confirm';
	}
} else if( isset($_POST['send']) && $_POST['send'] ){
	// 送信ボタンを押したとき
  if( !$_POST['token'] || !$_SESSION['token'] || !$_SESSION['email'] ){
	  $errmessage[] = '不正な処理が行われました';
	  $_SESSION     = array();
	  $mode         = 'input';
  } else if( $_POST['token'] != $_SESSION['token'] ){
    $errmessage[] = '不正な処理が行われました';
    $_SESSION     = array();
    $mode         = 'input';
  } else {
	  $message = "お問い合わせを受け付けました \r\n"
							 . "内容を確認のうえ、担当者より追ってご連絡させて頂きます。 \r\n"
							 . "しばらくお待ちください。 \r\n"
							 . "株式会社　兆久 \r\n"
							 . "\r\n"
	             . "名前: " . $_SESSION['fullname'] . "\r\n"
	             . "フリガナ: " . $_SESSION['furigananame'] . "\r\n"
	             . "会社名: " . $_SESSION['companyname'] . "\r\n"
	             . "電話番号: " . $_SESSION['phone'] . "\r\n"
	             . "email: " . $_SESSION['email'] . "\r\n"
	             . "お問い合わせ内容:\r\n"
	             . preg_replace( "/\r\n|\r|\n/", "\r\n", $_SESSION['message'] );

	  mail( $_SESSION['email'], 'お問い合わせありがとうございます', $message );
	  mail( 'chokyu1716@gmail.com', 'お問い合わせ内容', $message );
	  $_SESSION = array();
	  $mode     = 'send';
  }
} else {
	$_SESSION['fullname'] = "";
	$_SESSION['furigananame'] = "";
	$_SESSION['companyname'] = "";
	$_SESSION['phone'] = "";
	$_SESSION['email']    = "";
	$_SESSION['message']  = "";
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>お問い合わせ</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
  <style>
    body{
      padding: 10px;
      max-width: 600px;
      margin: 0px auto;
    }
    div.button{
      text-align: center;
    }
		div.thanks{
			text-align: center;
		}
  </style>
</head>
<body>
<?php if( $mode == 'input' ){ ?>
  <!-- 入力画面 -->
	<?php
	if( $errmessage ){
		echo '<div class="alert alert-danger" role="alert">';
		echo implode('<br>', $errmessage );
		echo '</div>';
	}
	?>
  <form action="./contactform.php" method="post">
    名前（必須）    <input type="text"    name="fullname" value="<?php echo $_SESSION['fullname'] ?>"  class="form-control"><br>
    フリガナ（必須）    <input type="text"    name="furigananame" value="<?php echo $_SESSION['furigananame'] ?>"  class="form-control"><br>
    会社名（任意）    <input type="text"    name="companyname" value="<?php echo $_SESSION['companyname'] ?>"  class="form-control"><br>
    電話番号（必須）    <input type="tel"    name="phone" value="<?php echo $_SESSION['phone'] ?>"  class="form-control"><br>
    Eメール（必須） <input type="email"   name="email"    value="<?php echo $_SESSION['email'] ?>"  class="form-control"><br>
    お問い合わせ内容。（必須）<br>
    <textarea cols="40" rows="8" name="message"  class="form-control"><?php echo $_SESSION['message'] ?></textarea><br>
    <div class="button">
      <input type="submit" name="confirm" value="確認" class="btn btn-primary btn-lg"/>
    </div>
  </form>
<?php } else if( $mode == 'confirm' ){ ?>
  <!-- 確認画面 -->
  <form action="./contactform.php" method="post">
    <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
    <h4>お問い合わせ内容をご確認いただき<br>
		お間違えがなければ送信へとお進みくださいませ。</h4>
		<br>
		<br>
		<h5>名前：</h5>  【<?php echo $_SESSION['fullname'] ?>】<br>
    <br>
		<h5>フリガナ：</h5>  【<?php echo $_SESSION['furigananame'] ?>】<br>
    <br>
		<h5>会社名：</h5>  【<?php echo $_SESSION['companyname'] ?>】<br>
    <br>
		<h5>電話番号：</h5>  【<?php echo $_SESSION['phone'] ?>】<br>
    <br>
		<h5>Eメール：</h5>  【<?php echo $_SESSION['email'] ?>】<br>
    <br>
		<h5>お問い合わせ内容：</h5>
	  【<?php echo nl2br($_SESSION['message']) ?>】<br>
		<br>
		<br>
    <input type="submit" name="back" value="戻る" class="btn btn-primary btn-lg"/>
    <input type="submit" name="send" value="送信" class="btn btn-primary btn-lg"/>
  </form>
<?php } else { ?>
  <!-- 完了画面 -->
	<div class="thanks">
		<h1>お問い合わせ 送信完了</h1>
		<p>
		お問い合わせありがとうございました。<br>
		内容を確認のうえ、担当者より追ってご連絡させて頂きます。<br>
		しばらくお待ちください。<br>
		<br>
		<br>
		株式会社　兆久
		</p>
	</div>
<?php } ?>
</body>
</html>


