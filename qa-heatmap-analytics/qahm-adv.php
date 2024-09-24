<?php
/** 
 *  @package qa_heatmap
 * 
 * ---------------------------------
 * くーすけ（吹き出し）お知らせ
 */

//お知らせ文の入力
//<div class="qusuke-fukidashi"> の中にお知らせ文(html)を入れてください。

$img_url = $this->get_img_dir_url();

if( ! $this->is_subscribed() ) {
?>
<style>
  #qusuke-info{
    position: relative;
    z-index: 9;
    display: flex;
    flex-direction: row-reverse;
    min-width: 300px;
    max-width: 450px;
    min-height: 140px;
    float: right;
    margin: 60px 5px 0 10px;
    background-color: #fff;
  }

  .qusuke-info-content{
    display: flex;
  }

  .qusuke-icon{
    width: 85px;
    height: 85px;
    margin: 45px 0 10px 10px;
  }

  .qusuke-fukidashi{
    position: relative;
    margin: 1em;
    padding: .5em 1em;
    min-width: 225px;
    max-width: 400px;
    color: #555;
    font-size: 16px;
    background: #FFF;
    border: solid 3px #eb8281;
    box-sizing: border-box;
	  border-radius: 10px;
    box-shadow: 2px 2px 5px 0 #999; 
  }  
  .qusuke-fukidashi:before {
    content: "";
    position: absolute;
    top: 50%;
    right: -24px;
    margin-top: -12px;
    border: 12px solid transparent;
    border-left: 12px solid #FFF;
    z-index: 2;
  }
  .qusuke-fukidashi:after {
    content: "";
    position: absolute;
    top: 50%;
    right: -30px;
    margin-top: -14px;
    border: 14px solid transparent;
    border-left: 14px solid #eb8281;
    z-index: 1;
  }

  .qusuke-fukidashi h4 {
    margin: 0.5em 0;
  }
  .font-bold {
    font-weight: bold;
  }
</style>
<div id="qusuke-info">
<div class="qusuke-info-content">
    <div class="qusuke-fukidashi">
		<p>
			<strong>QAアナリティクスをお友達に紹介すると、PV上限を無料で引き上げることができます！</strong><br>
			<span style="font-size:0.8em;">容量がもっと必要な場合はアップグレードしてください。</span>
		</p>
		<div style="display: flex; margin-bottom: 1em;">
			<a class="button button-primary" style="display:block; line-height: 2.4;" href="https://quarka.org/referral-program/" target="_blank" rel="noopener">お友達に紹介する</a>
			<a class="button" style="display:block; margin-left:auto; line-height: 2.4;" href="https://quarka.org/plan/" target="_blank" rel="noopener">アップグレードする</a>
		</div>
    </div>
    <div class="qusuke-icon">
		<span><img src="<?php echo $img_url.'q_suke.jpg'; ?>" width="85" height="85" alt=""/></span>
	</div>
 	</div>
</div>


<?php
}

/** ---------------------------------
 * 「ボタン付き」くーすけ（吹き出し）お知らせ
 */
$qusuke_button_adv = false;
if ( $qusuke_button_adv ) {
 //お知らせ開始日の設定
 $on_air_date = '2021-8-4';

 //有効期限日（最終日）の設定
 $expiration_date = '2021-9-17';
 
 //お知らせ文の入力
 //<div class="qusuke-fukidashi"> の中にお知らせ文(html)を入れてください。
 

 $img_url = $this->get_img_dir_url(); 
 $today       = new DateTime();
 $today->setTimezone(new DateTimeZone('Asia/Tokyo'));
 $on_air_date = new DateTime($on_air_date, new DateTimeZone('Asia/Tokyo'));
 $expiration_date  = new DateTime($expiration_date, new DateTimeZone('Asia/Tokyo'));
 $expiration_date->add(new DateInterval('P1D'));

 if ( ($today >= $on_air_date) && ($today < $expiration_date) ) :
?>
<style>
  #qusuke-info{
    position: relative;
    z-index: 9;
    display: flex;
    flex-direction: row-reverse;
    min-width: 300px;
    min-height: 140px;
    float: right;
    margin: 40px 5px 0 10px;
    background-color: #fff;
    /*
	  box-shadow: 2px 2px 5px 0 #999; 
    */
  }

  .qusuke-info-content{
    display: flex;
  }

  .qusuke-icon{
    width: 85px;
    height: 85px;
    margin: 45px 0 10px 10px;
  }

  .qusuke-fukidashi{
    position: relative;
    margin: 1em;
    padding: .5em 1em;
    min-width: 225px;
    max-width: 400px;
    color: #555;
    font-size: 16px;
    background: #FFF;
    border: solid 3px #eb8281;
    box-sizing: border-box;
	  border-radius: 10px;
    box-shadow: 2px 2px 5px 0 #999; 
  }  
  .qusuke-fukidashi:before {
    content: "";
    position: absolute;
    top: 50%;
    right: -24px;
    margin-top: -12px;
    border: 12px solid transparent;
    border-left: 12px solid #FFF;
    z-index: 2;
  }
  .qusuke-fukidashi:after {
    content: "";
    position: absolute;
    top: 50%;
    right: -30px;
    margin-top: -14px;
    border: 14px solid transparent;
    border-left: 14px solid #eb8281;
    z-index: 1;
  }

  .qusuke-fukidashi h4 {
    margin: 0.5em 0;
  }
		
  #qusuke-info .Button-style {
    font-size: 18px;
    display: block;
    margin: auto;
    padding: 0.5em 1em 0.3em;
    color: #ffffff;
    border-radius: 25px;
    background-color: #eb8281;
    border: none;
    cursor: pointer;
  }
</style>
<div id="qusuke-info">
<div class="qusuke-info-content">
    <div class="qusuke-fukidashi">
      <h4>☆キャンペーン実施中☆</h4>
      <p> 美味しいギフトが当たる！<br>
        2021年9月17日までにご応募ください</p>
      <a href="https://docs.google.com/forms/d/e/1FAIpQLSdghf_bvNUW-VNPbwgwuzJb9UccSu7VfXo_JxWkMPgru6RslQ/viewform?usp=sf_link" target="_blank" rel="noopener noreferrer"><input type="button" value="応募する！" class="Button-style"></a>
    </div>
    <div class="qusuke-icon"><span><img src="<?php echo $img_url.'q_suke.jpg'; ?>" width="85" height="85" alt=""/></span></div>
  </div>
</div>
<?php
 endif; 
}
?>
