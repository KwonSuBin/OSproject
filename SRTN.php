<html>
<head>
  <!--출력 화면의 디자인 -->
  <style media="screen">
    body {
      margin: 100px;
    }
    div#main {
      background-color: #eeddf9;
      padding:40px;
      border: 2px dotted #ababab;

    }
  </style>
</head>
<body>
  <div id="main">
<h2>SRTN</h2>

<!-- 폼에서 정보를 받아 HRRN에서 실행 -->
<!-- 사용자에게 입력 받을 항목들의 input type과 name을 지정 -->
<form action="SRTN.php" method="post">
  <p># of processes (N): <input type="number" name="p_num" ></p>  <!-- 프로세스의 수를 입력-->
  <p>Arrival time for each process: <input name='p_atime' type='text' ></p> <!-- 프로세스의 도착시간을 입력 -->
  <p>Burst time for each process: <input name='p_btime' type='text' ></p> <!-- 프로세스의 소요시간을 입력 -->
  <p><input type="submit" value="input"/></p> <!-- 입력받은 값들을 전달 -->
</form>

<!-- 그래프 테이블을 설정하기 위한 코드 -->
<div class="col-lg-7">

  <table border='1' cellspacing="0" width="770" height="50">
    <tr>


<?php
// form에서 받은 값들을 변수에 저장
$p_num = $_POST['p_num']; // 사용자가 입력한 프로세스의 수
$p_atime = $_POST['p_atime']; //입력 받은 각 프로세스의 arrival time
$p_atime = explode(" ", $p_atime);//text인 arrival time을 설정한 구분자를 이용하여 array로 만듦
$p_btime = $_POST['p_btime']; //각 프로세스의 잔여 실행시간 표시
$p_btime = explode(" ", $p_btime); //text인 burst time을 설정한 구분자를 이용하여 array로 만듦
$Fixed_btime = array(); //입력 받은 각 프로세스의 burst time 저장
$waiting_Queue = array();//대기Queue
$WT = array(); //각 프로세스의 waiting time
$TT = array(); //각 프로세스의 turnaround time
$NTT = array(); // 각 프로세스의 단위시간당 대기한 시간 비율
$running = 0; //현재 실행되는 프로세스
$a = 0; //다음에 올 프로세스
$empty = 1; //현재 실행되고 있는 프로세스가 있는지 검사(1이면 동작하고 있는 프로세서가 없음.)

//현재 burst time을 Fixed_btime에 저장
for ($i=0; $i <$p_num ; $i++) {
   $Fixed_btime[$i]=$p_btime[$i];
 }

//WT를 0으로 초기화
for($i=0;$i < $p_num; $i++){
  $WT[$i] = 0;
}

//도착시간과 현재시각이 일치하면 대기큐에 삽입
for($time = 0;  ;$time++){
  if($a < $p_num && $time == $p_atime[$a]){
    array_push($waiting_Queue,$a);
    $a++;
    //다른 프로세스가 삽입되면 실행 중이던 프로세스 실행 중단
    $empty = 1;
  }

  //실행중인 프로세스가 없을 경우
  if($empty == 1){

    //대기큐에 프로세스가 없으면 종료
    if(count($waiting_Queue) == 0){
      break;
      }
      //대기큐에 대기 중인 프로세스(burst time이 남은)가 하나만 있으면 실행시켜준다.
      else if(count($waiting_Queue)==1){
        for($i = 0; $i < $p_num; $i++){
          if($p_btime[$waiting_Queue[$i]] > 0)
          $running = $waiting_Queue[$i];
        }
      }
      //대기큐에 대기 중인 프로세스가 여러개일 경우
      else{
        //burst time을 비교할 대상(min)를 설정
        for($i = 0; $i < $p_num; $i++){
          if($p_btime[$waiting_Queue[$i]] > 0)
            $min = $waiting_Queue[$i];
          }
          //대기 중인 프로세스 중 burst time을 비교해 더 작은 것을 찾는다.
          //burst time이 제일 작은 것을  다시 min로 설정해 다음 것과 계속해서 비교한다.
          for($i=0;$i < $p_num; $i++){
            if($p_btime[$waiting_Queue[$i]] > 0 && $p_btime[$min] > $p_btime[$waiting_Queue[$i]]){
              $min = $waiting_Queue[$i];
            }
          }
          //burst time이 작은 것을 실행 시킨다.
          $running = $min;
        }
        //실행 중인 프로세서가 있다는 것을 표시
        $empty = 0;
      }
      //프로세서가 실행되지 않을 경우 waiting time을 증가 시켜준다.
      for ($i=0; $i <$p_num; $i++) {
        if($running != $waiting_Queue[$i])
        $WT[$waiting_Queue[$i]]++;
      }

      //실행 중인 프로세서가 있으면 실행중인 프로세서의 burst time을 차감
      if($empty == 0){
        $p_btime[$running]--;
      }

      //프로세스의 burst time이 0보다 작거나 같으면(실행이 끝나면)
      //검색해 찾아준 다음 key에 넣고 대기큐에서 삭제한다.
      if($p_btime[$running] <= 0){
        $key = array_search($running, $waiting_Queue); // $key = 2;
        unset($waiting_Queue[$key]);

        //실행 중인 프로세서 없음
        $empty = 1;
      }

      //그래프 출력을 위한 색 설정.
      if($running == 0){
        echo("<td bgcolor='#FE2E2E'></td>");
      }
      elseif($running == 1){
        echo("<td bgcolor='#FF8000'></td>");
      }
      elseif($running == 2){
        echo("<td bgcolor='#F7FE2E'></td>");
      }
      elseif($running == 3){
        echo("<td bgcolor='#5FB404'></td>");
      }
      elseif($running == 4){
        echo("<td bgcolor='#006400'></td>");
      }
      elseif($running == 5){
        echo("<td bgcolor='#1E90FF'></td>");
      }
      elseif($running == 6){
        echo("<td bgcolor='#08088A'></td>");
      }
      elseif($running == 7){
        echo("<td bgcolor='#FF0080'></td>");
      }
      elseif($running == 8){
        echo("<td bgcolor='#DA70D6'></td>");
      }
      elseif($running == 9){
        echo("<td bgcolor='#00CED1'></td>");
      }
      elseif($running == 10){
        echo("<td bgcolor='#C0C0C0'></td>");
      }
    }

    echo("</tr>
    </table>
    </div>
    <br>");

//TT와 NTT를 계산
for($i = 0; $i < $p_num; $i++){
  $TT[$i] = $Fixed_btime[$i] + $WT[$i];
  $NTT[$i] = $TT[$i] / $Fixed_btime[$i];
}

//테이블 헤드를 출력
echo("<table border='1' width = '800'>
<tr bgcolor ='#ccccc' align='center'>
  <td>Process ID</td>
  <td>Arrival time</td>
  <td>Burst time</td>
  <td>Waiting time</td>
  <td>Turnaround time</td>
  <td>Normalized TT</td>
</tr>");

//각각의 값을 표로 출력
for($i = 0; $i < $p_num; $i++){
  echo("<tr>
  <td width = '50' align ='center'>p$i</td>
  <td width = '50' align ='center'>$p_atime[$i]</td>
  <td width = '50' align ='center'>$Fixed_btime[$i]</td>
  <td width = '50' align ='center'>$WT[$i]</td>
  <td width = '50' align ='center'>$TT[$i]</td>
  <td width = '50' align ='center'>$NTT[$i]</td>
  </tr>
  ");
}
echo("</table>");
echo("<br>");
echo("<table border='1'cellspacing='0'>");

//어떤 프로세서가 어떤 색상을 그래프에서 사용하는지 명시
if($p_num >= 1)
  echo("<td>p0</td> <td width='20px' bgcolor='#FE2E2E'></td>");
if($p_num >= 2)
  echo("<td></td> <td>p1 </td><td width='20px' bgcolor='#FF8000'></td>");
if($p_num >= 3)
  echo("<td></td> <td>p2  </td><td width='20px' bgcolor='#F7FE2E'></td>");
if($p_num >= 4)
  echo("<td></td> <td>p3  </td><td width='20px' bgcolor='#5FB404'></td>");
if($p_num >= 5)
  echo("<td></td> <td>p4  </td><td width='20px' bgcolor='#006400'></td>");
if($p_num >= 6)
  echo("<td></td> <td>p5  </td><td width='20px' bgcolor='#1E90FF'></td>");
if($p_num >= 7)
  echo("<td></td> <td>p6  </td><td width='20px' bgcolor='#08088A'></td>");
if($p_num >= 8)
  echo("<td></td> <td>p7  </td><td width='20px' bgcolor='#FF0080'></td>");
if($p_num >= 9)
  echo("<td></td> <td>p8  </td><td width='20px' bgcolor='#DA70D6'></td>");
if($p_num >= 10)
  echo("<td></td> <td>p9  </td><td width='20px' bgcolor='#00CED1'></td>");
if($p_num >= 11)
  echo("<td></td> <td>p10  </td><td width='20px' bgcolor='#C0C0C0'></td>");

echo("</table>");
?>

  <!-- 뒤로가기 버튼 설정 -->
 <br>
 <a href="./index.php">↩︎</a>
</div>
</body>
</html>
