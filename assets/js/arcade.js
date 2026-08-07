document.addEventListener('DOMContentLoaded',function(){
  const arc=document.querySelector('.arcade-section');
  if(!arc) return;
  const menuEl=arc.querySelector('#menu');
  const gameEl=arc.querySelector('#gameContainer');
  let cleanupCurrent=null;

  function showMenu(){
    if(cleanupCurrent){cleanupCurrent();cleanupCurrent=null;}
    gameEl.style.display='none';gameEl.innerHTML='';menuEl.style.display='flex';
  }
  function showGame(name){
    if(cleanupCurrent){cleanupCurrent();cleanupCurrent=null;}
    menuEl.style.display='none';gameEl.style.display='flex';gameEl.innerHTML='';
    const initFns={tetris:initTetris,ttt:initTTT,chess:initChess,dama:initDama,snake:initSnake,maze:initMaze};
    cleanupCurrent=initFns[name](gameEl)||null;
  }
  arc.querySelectorAll('.cabinet-card').forEach(function(btn){
    btn.addEventListener('click',function(){showGame(btn.dataset.game);});
  });

  function initTTT(container){
    container.innerHTML='<div class="game-header"><button class="back-btn" id="backBtn">&larr; Cabinet Row</button><h2 class="game-title">TIC-TAC-TOE</h2><div class="status" id="tttStatus">You\'re X — your turn</div></div><div class="ttt-grid" id="tttGrid"></div><p class="hint">You play X and go first. The computer plays O.</p><button class="restart-btn" id="tttRestart">Restart</button>';
    var board=Array(9).fill(null),turn='X',over=false,thinking=false,timeoutId=null;
    var grid=container.querySelector('#tttGrid'),statusEl=container.querySelector('#tttStatus');
    function checkWin(b){var lines=[[0,1,2],[3,4,5],[6,7,8],[0,3,6],[1,4,7],[2,5,8],[0,4,8],[2,4,6]];for(var i=0;i<lines.length;i++){var a=lines[i][0],b1=lines[i][1],c=lines[i][2];if(b[a]&&b[a]===b[b1]&&b[a]===b[c])return b[a];}return null;}
    function render(){grid.innerHTML='';board.forEach(function(v,i){var cell=document.createElement('div');cell.className='ttt-cell'+(v==='X'?' x':v==='O'?' o':'');cell.textContent=v||'';cell.addEventListener('click',function(){handleClick(i);});grid.appendChild(cell);});}
    function minimax(b,player){var winner=checkWin(b);if(winner==='X')return{score:-1};if(winner==='O')return{score:1};if(b.every(function(v){return v;}))return{score:0};var avail=[];b.forEach(function(v,i){if(!v)avail.push(i);});var results=avail.map(function(i){var copy=b.slice();copy[i]=player;return{index:i,score:minimax(copy,player==='O'?'X':'O').score};});if(player==='O')return results.reduce(function(best,m){return m.score>best.score?m:best;},results[0]);return results.reduce(function(best,m){return m.score<best.score?m:best;},results[0]);}
    function finishIfOver(){var winner=checkWin(board);if(winner){over=true;statusEl.textContent=(winner==='X'?'You win!':'Computer wins!');return true;}if(board.every(function(v){return v;})){over=true;statusEl.textContent="It's a draw";return true;}return false;}
    function aiMove(){thinking=true;statusEl.textContent='Computer is thinking…';timeoutId=setTimeout(function(){var best=minimax(board,'O');if(best&&best.index!==undefined)board[best.index]='O';thinking=false;if(!finishIfOver()){turn='X';statusEl.textContent="Your turn";}render();},350);}
    function handleClick(i){if(over||board[i]||thinking||turn!=='X')return;board[i]='X';render();if(finishIfOver())return;turn='O';aiMove();}
    container.querySelector('#tttRestart').addEventListener('click',function(){clearTimeout(timeoutId);board=Array(9).fill(null);turn='X';over=false;thinking=false;statusEl.textContent="You're X — your turn";render();});
    container.querySelector('#backBtn').addEventListener('click',showMenu);
    render();
    return function(){clearTimeout(timeoutId);};
  }

  function initSnake(container){
    container.innerHTML='<div class="game-header"><button class="back-btn" id="backBtn">&larr; Cabinet Row</button><h2 class="game-title">SNAKE</h2><div class="status" id="snakeStatus">Score: 0</div></div><canvas id="snakeCanvas" width="400" height="400"></canvas><p class="hint">Arrow keys to move</p><button class="restart-btn" id="snakeRestart">Restart</button>';
    var canvas=container.querySelector('#snakeCanvas'),ctx=canvas.getContext('2d'),statusEl=container.querySelector('#snakeStatus');
    var cell=20,cols=20,rows=20,snake,dir,nextDir,food,score,over,timer;
    function placeFood(){var ok=false;while(!ok){food={x:Math.floor(Math.random()*cols),y:Math.floor(Math.random()*rows)};ok=!snake.some(function(s){return s.x===food.x&&s.y===food.y;});}}
    function reset(){snake=[{x:10,y:10},{x:9,y:10},{x:8,y:10}];dir={x:1,y:0};nextDir={x:1,y:0};placeFood();score=0;over=false;statusEl.textContent='Score: 0';if(timer)clearInterval(timer);timer=setInterval(tick,110);draw();}
    function tick(){if(over)return;dir=nextDir;var head={x:snake[0].x+dir.x,y:snake[0].y+dir.y};if(head.x<0||head.x>=cols||head.y<0||head.y>=rows||snake.some(function(s){return s.x===head.x&&s.y===head.y;})){over=true;clearInterval(timer);statusEl.textContent='Game over — score '+score;return;}snake.unshift(head);if(head.x===food.x&&head.y===food.y){score++;statusEl.textContent='Score: '+score;placeFood();}else snake.pop();draw();}
    function draw(){    ctx.fillStyle='#0a0a0a';ctx.fillRect(0,0,canvas.width,canvas.height);ctx.strokeStyle='rgba(238,238,238,0.06)';ctx.lineWidth=1;for(var x=0;x<=cols;x++){ctx.beginPath();ctx.moveTo(x*cell+0.5,0);ctx.lineTo(x*cell+0.5,canvas.height);ctx.stroke();}for(var y=0;y<=rows;y++){ctx.beginPath();ctx.moveTo(0,y*cell+0.5);ctx.lineTo(canvas.width,y*cell+0.5);ctx.stroke();}ctx.fillStyle='#e53935';ctx.fillRect(food.x*cell,food.y*cell,cell-2,cell-2);snake.forEach(function(s,i){ctx.fillStyle=i===0?'#00e676':'#00c853';ctx.fillRect(s.x*cell,s.y*cell,cell-2,cell-2);});}
    function keyHandler(e){var k=e.key;if(k==='ArrowUp'&&dir.y===0){nextDir={x:0,y:-1};e.preventDefault();}else if(k==='ArrowDown'&&dir.y===0){nextDir={x:0,y:1};e.preventDefault();}else if(k==='ArrowLeft'&&dir.x===0){nextDir={x:-1,y:0};e.preventDefault();}else if(k==='ArrowRight'&&dir.x===0){nextDir={x:1,y:0};e.preventDefault();}}
    window.addEventListener('keydown',keyHandler);
    container.querySelector('#snakeRestart').addEventListener('click',reset);
    container.querySelector('#backBtn').addEventListener('click',showMenu);
    reset();
    return function(){clearInterval(timer);window.removeEventListener('keydown',keyHandler);};
  }

  function initTetris(container){
    container.innerHTML='<div class="game-header"><button class="back-btn" id="backBtn">&larr; Cabinet Row</button><h2 class="game-title">TETRIS</h2><div class="status" id="tetrisStatus">Score: 0</div></div><div class="tetris-layout"><div class="tetris-side"><div class="next-label">HOLD</div><canvas id="tetrisHold" width="96" height="96"></canvas></div><canvas id="tetrisCanvas" width="240" height="480"></canvas><div class="tetris-side"><div class="next-label">NEXT</div><canvas id="tetrisNext" width="96" height="96"></canvas></div></div><p class="hint">&larr; &rarr; move · &uarr; rotate · &darr; soft drop · space hard drop · C hold</p><button class="restart-btn" id="tetrisRestart">Restart</button>';
    var canvas=container.querySelector('#tetrisCanvas'),ctx=canvas.getContext('2d');
    var nextCanvas=container.querySelector('#tetrisNext'),nctx=nextCanvas.getContext('2d');
    var holdCanvas=container.querySelector('#tetrisHold'),hctx=holdCanvas.getContext('2d');
    var statusEl=container.querySelector('#tetrisStatus'),cols=10,rows=20,cell=24;
    var SHAPES={I:{cells:[[0,1],[1,1],[2,1],[3,1]],color:'#00e676'},O:{cells:[[1,1],[2,1],[1,2],[2,2]],color:'#00c853'},T:{cells:[[1,1],[0,2],[1,2],[2,2]],color:'#00e676'},S:{cells:[[1,1],[2,1],[0,2],[1,2]],color:'#00c853'},Z:{cells:[[0,1],[1,1],[1,2],[2,2]],color:'#00e676'},J:{cells:[[0,1],[0,2],[1,2],[2,2]],color:'#eee'},L:{cells:[[2,1],[0,2],[1,2],[2,2]],color:'#00c853'}};
    var keys=Object.keys(SHAPES),board,current,next,score,over,raf,lastDrop,dropInterval=500;
    var heldPiece=null,holdUsed=false;
    function newPiece(){var k=keys[Math.floor(Math.random()*keys.length)],s=SHAPES[k];return{cells:s.cells.map(function(c){return c.slice();}),color:s.color,x:3,y:-1,key:k};}
    function collide(piece,ox,oy,cells){return cells.some(function(c){var bx=piece.x+c[0]+ox,by=piece.y+c[1]+oy;if(bx<0||bx>=cols||by>=rows)return true;if(by>=0&&board[by][bx])return true;return false;});}
    function merge(){current.cells.forEach(function(c){var bx=current.x+c[0],by=current.y+c[1];if(by>=0)board[by][bx]=current.color;});}
    function clearLines(){var cleared=0;for(var r=rows-1;r>=0;r--){if(board[r].every(function(v){return v;})){board.splice(r,1);board.unshift(Array(cols).fill(null));cleared++;r++;}}if(cleared){score+=cleared*100;statusEl.textContent='Score: '+score;}}
    function spawnNext(){current=next||newPiece();next=newPiece();holdUsed=false;drawNext();drawHold();if(collide(current,0,0,current.cells)){over=true;statusEl.textContent='Game over — score '+score;}}
    function drop(){if(!collide(current,0,1,current.cells))current.y++;else{merge();clearLines();spawnNext();}}
    function hardDrop(){while(!collide(current,0,1,current.cells))current.y++;drop();draw();}
    function rotate(){var rotated=current.cells.map(function(c){return[3-c[1],c[0]];});if(!collide(current,0,0,rotated))current.cells=rotated;}
    function move(dx){if(!collide(current,dx,0,current.cells))current.x+=dx;}
    function hold(){if(holdUsed||over)return;holdUsed=true;if(heldPiece){var tmp=heldPiece;heldPiece={cells:SHAPES[current.key].cells.map(function(c){return c.slice();}),color:current.color,key:current.key};current={cells:SHAPES[tmp.key].cells.map(function(c){return c.slice();}),color:tmp.color,x:3,y:-1,key:tmp.key};}else{heldPiece={cells:SHAPES[current.key].cells.map(function(c){return c.slice();}),color:current.color,key:current.key};spawnNext();}drawHold();}
    function drawGrid(){ctx.strokeStyle='rgba(238,238,238,0.06)';ctx.lineWidth=1;for(var x=0;x<=cols;x++){ctx.beginPath();ctx.moveTo(x*cell+0.5,0);ctx.lineTo(x*cell+0.5,canvas.height);ctx.stroke();}for(var y=0;y<=rows;y++){ctx.beginPath();ctx.moveTo(0,y*cell+0.5);ctx.lineTo(canvas.width,y*cell+0.5);ctx.stroke();}}
    function draw(){ctx.fillStyle='#0a0a0a';ctx.fillRect(0,0,canvas.width,canvas.height);drawGrid();for(var r=0;r<rows;r++)for(var c=0;c<cols;c++){if(board[r][c]){ctx.fillStyle=board[r][c];ctx.fillRect(c*cell,r*cell,cell-1,cell-1);}}ctx.fillStyle=current.color;current.cells.forEach(function(cx){var bx=current.x+cx[0],by=current.y+cx[1];if(by>=0)ctx.fillRect(bx*cell,by*cell,cell-1,cell-1);});}
    function drawPiece(ctx2,canvas2,piece){ctx2.fillStyle='#0a0a0a';ctx2.fillRect(0,0,canvas2.width,canvas2.height);if(!piece)return;var pc=20;var xs=piece.cells.map(function(c){return c[0];}),ys=piece.cells.map(function(c){return c[1];});var minX=Math.min.apply(null,xs),maxX=Math.max.apply(null,xs),minY=Math.min.apply(null,ys),maxY=Math.max.apply(null,ys);var w=(maxX-minX+1)*pc,h=(maxY-minY+1)*pc;var offX=(canvas2.width-w)/2,offY=(canvas2.height-h)/2;ctx2.fillStyle=piece.color;piece.cells.forEach(function(cx){ctx2.fillRect(offX+(cx[0]-minX)*pc,offY+(cx[1]-minY)*pc,pc-2,pc-2);});}
    function drawNext(){drawPiece(nctx,nextCanvas,next);}
    function drawHold(){drawPiece(hctx,holdCanvas,heldPiece);}
    function loop(ts){if(over)return;if(!lastDrop)lastDrop=ts;if(ts-lastDrop>dropInterval){drop();lastDrop=ts;}draw();raf=requestAnimationFrame(loop);}
    function reset(){board=Array.from({length:rows},function(){return Array(cols).fill(null);});score=0;over=false;next=null;heldPiece=null;holdUsed=false;statusEl.textContent='Score: 0';drawHold();spawnNext();lastDrop=null;if(raf)cancelAnimationFrame(raf);raf=requestAnimationFrame(loop);}
    function keyHandler(e){if(over)return;if(e.key==='ArrowLeft'){move(-1);e.preventDefault();}else if(e.key==='ArrowRight'){move(1);e.preventDefault();}else if(e.key==='ArrowDown'){drop();e.preventDefault();}else if(e.key==='ArrowUp'){rotate();e.preventDefault();}else if(e.key===' '){hardDrop();e.preventDefault();}else if(e.key==='c'||e.key==='C'||e.key==='Shift'){hold();e.preventDefault();}draw();}
    window.addEventListener('keydown',keyHandler);
    container.querySelector('#tetrisRestart').addEventListener('click',reset);
    container.querySelector('#backBtn').addEventListener('click',showMenu);
    reset();
    return function(){cancelAnimationFrame(raf);window.removeEventListener('keydown',keyHandler);};
  }

  function initDama(container){
    var boardEl,statusEl,board,turn,selected,over,thinking,timeoutId,lastMove,humanColor=null,aiColor=null;
    function showPicker(){container.innerHTML='<div class="game-header"><button class="back-btn" id="backBtn">&larr; Cabinet Row</button><h2 class="game-title">DAMA</h2></div><p class="hint">Choose your side. Red always moves first.</p><div class="btn-row"><button class="restart-btn" id="pickRed">Play Red</button><button class="restart-btn" id="pickTeal">Play Teal</button></div>';container.querySelector('#backBtn').addEventListener('click',showMenu);container.querySelector('#pickRed').addEventListener('click',function(){startGame('red');});container.querySelector('#pickTeal').addEventListener('click',function(){startGame('teal');});}
    function startGame(color){humanColor=color;aiColor=color==='red'?'teal':'red';container.innerHTML='<div class="game-header"><button class="back-btn" id="backBtn">&larr; Cabinet Row</button><h2 class="game-title">DAMA</h2><div class="status" id="damaStatus">Your turn</div></div><div class="dama-board" id="damaBoard"></div><p class="hint">You play '+humanColor+'. The computer plays '+aiColor+'. Captures are mandatory. Kings fly like a queen.</p><button class="restart-btn" id="damaRestart">Restart</button>';boardEl=container.querySelector('#damaBoard');statusEl=container.querySelector('#damaStatus');container.querySelector('#damaRestart').addEventListener('click',reset);container.querySelector('#backBtn').addEventListener('click',showMenu);reset();}
    function reset(){clearTimeout(timeoutId);board=Array.from({length:8},function(){return Array(8).fill(null);});for(var r=0;r<3;r++)for(var c=0;c<8;c++)if((r+c)%2===1)board[r][c]={color:'red',king:false};for(var r2=5;r2<8;r2++)for(var c2=0;c2<8;c2++)if((r2+c2)%2===1)board[r2][c2]={color:'teal',king:false};turn='red';selected=null;over=false;thinking=false;lastMove=null;statusEl.textContent=(turn===humanColor)?'Your turn':"Computer's turn";render();if(turn===aiColor)aiTurn();}
    function dirFor(color){return color==='red'?1:-1;}
    function getMoves(r,c){var piece=board[r][c];if(!piece)return{steps:[],captures:[]};var steps=[],captures=[];if(piece.king){var allDirs=[[1,1],[1,-1],[-1,1],[-1,-1]];for(var d=0;d<allDirs.length;d++){var dr=allDirs[d][0],dc=allDirs[d][1],nr=r+dr,nc=c+dc;while(nr>=0&&nr<8&&nc>=0&&nc<8&&!board[nr][nc]){steps.push({r:nr,c:nc});nr+=dr;nc+=dc;}if(nr>=0&&nr<8&&nc>=0&&nc<8){var mid=board[nr][nc];if(mid&&mid.color!==piece.color){var lr=nr+dr,lc=nc+dc;while(lr>=0&&lr<8&&lc>=0&&lc<8&&!board[lr][lc]){captures.push({r:lr,c:lc,cap:{r:nr,c:nc}});lr+=dr;lc+=dc;}}}}}else{var drr=dirFor(piece.color);for(var dci=-1;dci<=1;dci+=2){var nrr=r+drr,ncr=c+dci;if(nrr>=0&&nrr<8&&ncr>=0&&ncr<8&&!board[nrr][ncr])steps.push({r:nrr,c:ncr});var jrr=r+drr*2,jcc=c+dci*2;if(nrr>=0&&nrr<8&&ncr>=0&&ncr<8&&jrr>=0&&jrr<8&&jcc>=0&&jcc<8){var mid2=board[nrr][ncr];if(mid2&&mid2.color!==piece.color&&!board[jrr][jcc])captures.push({r:jrr,c:jcc,cap:{r:nrr,c:ncr}});}}}return{steps:steps,captures:captures};}
    function anyCaptureAvailable(color){for(var r=0;r<8;r++)for(var c=0;c<8;c++){if(board[r][c]&&board[r][c].color===color&&getMoves(r,c).captures.length)return true;}return false;}
    function render(){boardEl.innerHTML='';var mustCapture=anyCaptureAvailable(turn);for(var r=0;r<8;r++){for(var c=0;c<8;c++){var sq=document.createElement('div');sq.className='dama-sq '+(((r+c)%2===1)?'dark':'light');var piece=board[r][c];if(piece){var p=document.createElement('div');p.className='dama-piece '+piece.color+(piece.king?' king':'');sq.appendChild(p);}if(selected&&selected.r===r&&selected.c===c)sq.classList.add('selected');if(lastMove&&((lastMove.from.r===r&&lastMove.from.c===c)||(lastMove.to.r===r&&lastMove.to.c===c)))sq.classList.add('last-move');(function(rr,cc){sq.addEventListener('click',function(){handleClick(rr,cc,mustCapture);});})(r,c);boardEl.appendChild(sq);}}if(selected){var moves=getMoves(selected.r,selected.c);var targets=mustCapture?moves.captures:(moves.captures.length?moves.captures:moves.steps);targets.forEach(function(t){boardEl.children[t.r*8+t.c].classList.add('valid-target');});}}
    function hasAnyMove(color){for(var r=0;r<8;r++)for(var c=0;c<8;c++){var p=board[r][c];if(p&&p.color===color){var m=getMoves(r,c);if(m.steps.length||m.captures.length)return true;}}return false;}
    function announceWinner(winnerColor){statusEl.textContent=(winnerColor===humanColor)?'You win!':'Computer wins!';}
    function checkGameOver(){var redCount=0,tealCount=0;for(var r=0;r<8;r++)for(var c=0;c<8;c++){var p=board[r][c];if(p&&p.color==='red')redCount++;if(p&&p.color==='teal')tealCount++;}if(redCount===0){over=true;announceWinner('teal');return;}if(tealCount===0){over=true;announceWinner('red');return;}var other=turn==='red'?'teal':'red';if(!hasAnyMove(other)){over=true;announceWinner(turn);}}
    function applyMove(selR,selC,target){lastMove={from:{r:selR,c:selC},to:{r:target.r,c:target.c}};board[target.r][target.c]=board[selR][selC];board[selR][selC]=null;if(target.cap)board[target.cap.r][target.cap.c]=null;var pp=board[target.r][target.c];if((pp.color==='red'&&target.r===7)||(pp.color==='teal'&&target.r===0))pp.king=true;return!!(target.cap&&getMoves(target.r,target.c).captures.length);}
    function handleClick(r,c,mustCapture){if(over||thinking||turn!==humanColor)return;var piece=board[r][c];if(selected){var moves=getMoves(selected.r,selected.c);var targets=mustCapture?moves.captures:(moves.captures.length?moves.captures:moves.steps);var target=null;for(var i=0;i<targets.length;i++){if(targets[i].r===r&&targets[i].c===c){target=targets[i];break;}}if(target){var chained=applyMove(selected.r,selected.c,target);if(chained){selected={r:r,c:c};statusEl.textContent='Capture again!';render();return;}selected=null;checkGameOver();if(!over){turn=aiColor;statusEl.textContent="Computer's turn";render();aiTurn();return;}render();return;}if(piece&&piece.color===turn){selected={r:r,c:c};render();return;}selected=null;render();return;}else if(piece&&piece.color===turn){var mv=getMoves(r,c);if(mustCapture&&mv.captures.length===0)return;selected={r:r,c:c};render();}}
    function aiTurn(){if(over||turn!==aiColor)return;thinking=true;timeoutId=setTimeout(function(){var mustCap=anyCaptureAvailable(aiColor);var candidates=[];for(var r=0;r<8;r++)for(var c=0;c<8;c++){var p=board[r][c];if(p&&p.color===aiColor){var mv=getMoves(r,c);var tgts=mustCap?mv.captures:(mv.captures.length?mv.captures:mv.steps);tgts.forEach(function(t){candidates.push({from:{r:r,c:c},target:t});});}}if(candidates.length===0){thinking=false;over=true;announceWinner(humanColor);render();return;}var bestScore=-1,bestCands=[];candidates.forEach(function(cd){var s=0;if(cd.target.cap)s+=10;var after=getMoves(cd.target.r,cd.target.c);if(after.captures.length)s+=6;var pc=board[cd.from.r][cd.from.c];if(pc&&!pc.king&&((pc.color==='red'&&cd.target.r===7)||(pc.color==='teal'&&cd.target.r===0)))s+=8;if(s>bestScore){bestScore=s;bestCands=[cd];}else if(s===bestScore){bestCands.push(cd);}});var choice=bestCands[Math.floor(Math.random()*bestCands.length)];var chained2=applyMove(choice.from.r,choice.from.c,choice.target);render();if(chained2){statusEl.textContent='Computer captures again…';timeoutId=setTimeout(function(){thinking=false;aiTurn();},500);return;}thinking=false;checkGameOver();if(!over){turn=humanColor;statusEl.textContent='Your turn';}render();},450);}
    showPicker();
    return function(){clearTimeout(timeoutId);};
  }

  function initMaze(container){
    container.innerHTML='<div class="game-header"><button class="back-btn" id="backBtn">&larr; Cabinet Row</button><h2 class="game-title">MAZE</h2><div class="status" id="mazeStatus">Level 1 — find the exit</div><div class="maze-timer" id="mazeTimer">0:00</div></div><div class="maze-frame"><canvas id="mazeCanvas" width="420" height="420"></canvas></div><p class="hint">Arrow keys / WASD to move · 3 levels · the clock does not care about you</p><button class="restart-btn" id="mazeRestart">Restart</button>';
    var canvas=container.querySelector('#mazeCanvas'),ctx=canvas.getContext('2d');
    var statusEl=container.querySelector('#mazeStatus'),timerEl=container.querySelector('#mazeTimer');
    var LEVELS=[{cols:9,rows:9},{cols:13,rows:13},{cols:19,rows:19}];
    var walls,player,exit,level,startTime,elapsed,timer=null,over=false;
    function genMaze(cols,rows){
      var grid=[];
      for(var r=0;r<rows;r++){grid[r]=[];for(var c=0;c<cols;c++)grid[r][c]={N:true,E:true,S:true,W:true,vis:false};}
      var stack=[{r:0,c:0}];grid[0][0].vis=true;
      while(stack.length){
        var cur=stack[stack.length-1],nbs=[];
        if(cur.r>0&&!grid[cur.r-1][cur.c].vis)nbs.push({r:cur.r-1,c:cur.c,d:'N'});
        if(cur.c<cols-1&&!grid[cur.r][cur.c+1].vis)nbs.push({r:cur.r,c:cur.c+1,d:'E'});
        if(cur.r<rows-1&&!grid[cur.r+1][cur.c].vis)nbs.push({r:cur.r+1,c:cur.c,d:'S'});
        if(cur.c>0&&!grid[cur.r][cur.c-1].vis)nbs.push({r:cur.r,c:cur.c-1,d:'W'});
        if(nbs.length===0){stack.pop();continue;}
        var next=nbs[Math.floor(Math.random()*nbs.length)];
        if(next.d==='N'){grid[cur.r][cur.c].N=false;grid[next.r][next.c].S=false;}
        else if(next.d==='E'){grid[cur.r][cur.c].E=false;grid[next.r][next.c].W=false;}
        else if(next.d==='S'){grid[cur.r][cur.c].S=false;grid[next.r][next.c].N=false;}
        else if(next.d==='W'){grid[cur.r][cur.c].W=false;grid[next.r][next.c].E=false;}
        grid[next.r][next.c].vis=true;stack.push(next);
      }
      return grid;
    }
    function fmt(s){var m=Math.floor(s/60),sec=s%60;return m+':'+(sec<10?'0':'')+sec;}
    function startLevel(){
      var cfg=LEVELS[level];
      walls=genMaze(cfg.cols,cfg.rows);
      player={r:0,c:0};exit={r:cfg.rows-1,c:cfg.cols-1};
      statusEl.textContent='Level '+(level+1)+' of 3 — find the exit';
      draw();
    }
    function reset(){
      if(timer)clearInterval(timer);timer=null;
      level=0;elapsed=0;over=false;
      startLevel();
      startTime=Date.now();
      timer=setInterval(function(){elapsed=Math.floor((Date.now()-startTime)/1000);timerEl.textContent=fmt(elapsed);},250);
    }
    function move(dr,dc){
      if(over)return;
      var cell=walls[player.r][player.c];
      if(dr===-1&&cell.N)return;
      if(dr===1&&cell.S)return;
      if(dc===-1&&cell.W)return;
      if(dc===1&&cell.E)return;
      player.r+=dr;player.c+=dc;draw();
      if(player.r===exit.r&&player.c===exit.c){
        if(level<LEVELS.length-1){
          level++;startLevel();
        }else{
          over=true;if(timer)clearInterval(timer);
          timerEl.textContent=fmt(elapsed);
          statusEl.textContent='You escaped all 3 levels in '+fmt(elapsed)+'!';
          draw();
        }
      }
    }
    function draw(){
      var cfg=LEVELS[level],pad=10,w=canvas.width,h=canvas.height;
      var cellW=(w-pad*2)/cfg.cols,cellH=(h-pad*2)/cfg.rows;
      ctx.fillStyle='#0a0a0a';ctx.fillRect(0,0,w,h);
      ctx.fillStyle='#e53935';ctx.fillRect(pad+exit.c*cellW+2,pad+exit.r*cellH+2,cellW-4,cellH-4);
      ctx.strokeStyle='#a7ffcd';ctx.lineWidth=2.5;ctx.lineJoin='round';ctx.lineCap='round';
      for(var r=0;r<cfg.rows;r++)for(var c=0;c<cfg.cols;c++){
        var x=pad+c*cellW,y=pad+r*cellH;
        ctx.beginPath();
        if(walls[r][c].N){ctx.moveTo(x,y);ctx.lineTo(x+cellW,y);}
        if(walls[r][c].S){ctx.moveTo(x,y+cellH);ctx.lineTo(x+cellW,y+cellH);}
        if(walls[r][c].W){ctx.moveTo(x,y);ctx.lineTo(x,y+cellH);}
        if(walls[r][c].E){ctx.moveTo(x+cellW,y);ctx.lineTo(x+cellW,y+cellH);}
        ctx.stroke();
      }
      ctx.fillStyle='#fff';
      ctx.beginPath();
      ctx.arc(pad+player.c*cellW+cellW/2,pad+player.r*cellH+cellH/2,Math.min(cellW,cellH)*0.3,0,Math.PI*2);
      ctx.fill();
    }
    function keyHandler(e){
      var k=e.key,handled=false;
      if(k==='ArrowUp'||k==='w'||k==='W'){move(-1,0);handled=true;}
      else if(k==='ArrowDown'||k==='s'||k==='S'){move(1,0);handled=true;}
      else if(k==='ArrowLeft'||k==='a'||k==='A'){move(0,-1);handled=true;}
      else if(k==='ArrowRight'||k==='d'||k==='D'){move(0,1);handled=true;}
      if(handled)e.preventDefault();
    }
    window.addEventListener('keydown',keyHandler);
    container.querySelector('#mazeRestart').addEventListener('click',reset);
    container.querySelector('#backBtn').addEventListener('click',showMenu);
    reset();
    return function(){if(timer)clearInterval(timer);window.removeEventListener('keydown',keyHandler);};
  }

  function initChess(container){
    var PIECE_SHAPES={p:'<circle cx="30" cy="18" r="7"/><path d="M22 34 Q30 25 38 34 Z"/><rect x="16" y="34" width="28" height="5" rx="2"/><rect x="13" y="41" width="34" height="6" rx="2"/>',r:'<rect x="17" y="20" width="26" height="16"/><rect x="15" y="11" width="6" height="10"/><rect x="27" y="11" width="6" height="10"/><rect x="39" y="11" width="6" height="10"/><rect x="15" y="18" width="30" height="4"/><rect x="13" y="41" width="34" height="6" rx="2"/>',n:'<path d="M18 41 L19 27 Q19 15 31 12 Q45 10 43 22 Q47 23 45 29 L38 29 L36 22 L29 25 L32 31 L25 41 Z"/><rect x="13" y="41" width="34" height="6" rx="2"/>',b:'<circle cx="30" cy="11" r="4"/><path d="M21 34 Q19 22 30 15 Q41 22 39 34 Z"/><rect x="16" y="34" width="28" height="5" rx="2"/><rect x="13" y="41" width="34" height="6" rx="2"/>',q:'<path d="M16 21 L21 33 L25 17 L30 33 L35 17 L39 33 L44 21 L40 34 L20 34 Z"/><circle cx="16" cy="19" r="2.5"/><circle cx="30" cy="14" r="2.5"/><circle cx="44" cy="19" r="2.5"/><rect x="16" y="34" width="28" height="5" rx="2"/><rect x="13" y="41" width="34" height="6" rx="2"/>',k:'<rect x="28" y="6" width="4" height="10"/><rect x="24" y="10" width="12" height="4"/><path d="M19 22 L41 22 L38 34 L22 34 Z"/><rect x="16" y="34" width="28" height="5" rx="2"/><rect x="13" y="41" width="34" height="6" rx="2"/>'};
    function pieceMarkup(piece){var isWhite=piece[0]==='w',fill=isWhite?'#ffffff':'#1a1a1a',stroke=isWhite?'#555555':'#00e676';return '<svg viewBox="0 0 60 50"><g fill="'+fill+'" stroke="'+stroke+'" stroke-width="2" stroke-linejoin="round" stroke-linecap="round">'+PIECE_SHAPES[piece[1]]+'</g></svg>';}
    var boardEl,statusEl,rulesPanel,board,turn,selected,over,legalTargets,thinking,timeoutId,castling,epTarget,lastMove,humanColor=null,aiColor=null;
    function showPicker(){container.innerHTML='<div class="game-header"><button class="back-btn" id="backBtn">&larr; Cabinet Row</button><h2 class="game-title">CHESS</h2></div><p class="hint">Choose your side. White always moves first.</p><div class="btn-row"><button class="restart-btn" id="pickWhite">Play White</button><button class="restart-btn" id="pickBlack">Play Black</button></div>';container.querySelector('#backBtn').addEventListener('click',showMenu);container.querySelector('#pickWhite').addEventListener('click',function(){startGame('w');});container.querySelector('#pickBlack').addEventListener('click',function(){startGame('b');});}
    function startGame(color){humanColor=color;aiColor=color==='w'?'b':'w';container.innerHTML='<div class="game-header"><button class="back-btn" id="backBtn">&larr; Cabinet Row</button><h2 class="game-title">CHESS</h2><div class="status" id="chessStatus">Your turn</div></div><div class="chess-board" id="chessBoard"></div><p class="hint">You play '+(humanColor==='w'?'White':'Black')+'. Full rules: castling, en passant, check &amp; checkmate. Pawns auto-promote to queen.</p><div class="btn-row"><button class="restart-btn" id="chessRestart">Restart</button><button class="rules-btn" id="chessRulesBtn">Official Rules</button></div><div class="rules-panel" id="chessRules" hidden><h3>HOW TO PLAY</h3><p>Two players take turns moving pieces on an 8×8 board. The goal is to checkmate the opponent\'s king.</p><ul><li><strong>Pawn</strong> — moves forward one square (two on first move), captures diagonally. Promotes to queen on far rank.</li><li><strong>Knight</strong> — L-shape, can jump.</li><li><strong>Bishop</strong> — slides diagonally.</li><li><strong>Rook</strong> — slides straight.</li><li><strong>Queen</strong> — combines rook + bishop.</li><li><strong>King</strong> — one square any direction, never into check.</li></ul><p><strong>Castling:</strong> king slides 2 toward rook, rook hops beside it. Must be first move for both, no pieces between, king not in/through/into check.</p><p><strong>En passant:</strong> pawn that advances 2 and lands beside yours can be captured as if it moved 1 — only on next move.</p><p><strong>Check &amp; checkmate:</strong> king in check must escape. No escape = checkmate. No legal moves but not in check = stalemate (draw).</p></div>';boardEl=container.querySelector('#chessBoard');statusEl=container.querySelector('#chessStatus');rulesPanel=container.querySelector('#chessRules');container.querySelector('#chessRestart').addEventListener('click',reset);container.querySelector('#backBtn').addEventListener('click',showMenu);container.querySelector('#chessRulesBtn').addEventListener('click',function(){rulesPanel.hidden=!rulesPanel.hidden;});reset();}
    function reset(){clearTimeout(timeoutId);board=[['br','bn','bb','bq','bk','bb','bn','br'],['bp','bp','bp','bp','bp','bp','bp','bp'],[null,null,null,null,null,null,null,null],[null,null,null,null,null,null,null,null],[null,null,null,null,null,null,null,null],[null,null,null,null,null,null,null,null],['wp','wp','wp','wp','wp','wp','wp','wp'],['wr','wn','wb','wq','wk','wb','wn','wr']];turn='w';selected=null;over=false;legalTargets=[];thinking=false;castling={w:{K:true,Q:true},b:{K:true,Q:true}};epTarget=null;lastMove=null;statusEl.textContent=(turn===humanColor)?'Your turn':"Computer's turn";render();if(turn===aiColor)aiTurn();}
    function inBounds(r,c){return r>=0&&r<8&&c>=0&&c<8;}
    function pseudoMoves(b,r,c){var piece=b[r][c];if(!piece)return[];var color=piece[0],type=piece[1],enemy=color==='w'?'b':'w',moves=[];function add(nr,nc){if(!inBounds(nr,nc))return;var t=b[nr][nc];if(!t)moves.push({r:nr,c:nc});else if(t[0]===enemy)moves.push({r:nr,c:nc,capture:true});}function slide(dr,dc){var nr=r+dr,nc=c+dc;while(inBounds(nr,nc)){var t=b[nr][nc];if(!t)moves.push({r:nr,c:nc});else{if(t[0]===enemy)moves.push({r:nr,c:nc,capture:true});break;}nr+=dr;nc+=dc;}}
    if(type==='p'){var dir=color==='w'?-1:1,startRow=color==='w'?6:1;if(inBounds(r+dir,c)&&!b[r+dir][c]){moves.push({r:r+dir,c:c});if(r===startRow&&!b[r+2*dir][c])moves.push({r:r+2*dir,c:c});}for(var dci=-1;dci<=1;dci+=2){var nrr=r+dir,ncr=c+dci;if(inBounds(nrr,ncr)&&b[nrr][ncr]&&b[nrr][ncr][0]===enemy)moves.push({r:nrr,c:ncr,capture:true});}}else if(type==='n'){[[1,2],[2,1],[-1,2],[-2,1],[1,-2],[2,-1],[-1,-2],[-2,-1]].forEach(function(d){add(r+d[0],c+d[1]);});}else if(type==='b'){[[1,1],[1,-1],[-1,1],[-1,-1]].forEach(function(d){slide(d[0],d[1]);});}else if(type==='r'){[[1,0],[-1,0],[0,1],[0,-1]].forEach(function(d){slide(d[0],d[1]);});}else if(type==='q'){[[1,0],[-1,0],[0,1],[0,-1],[1,1],[1,-1],[-1,1],[-1,-1]].forEach(function(d){slide(d[0],d[1]);});}else if(type==='k'){[[1,0],[-1,0],[0,1],[0,-1],[1,1],[1,-1],[-1,1],[-1,-1]].forEach(function(d){add(r+d[0],c+d[1]);});}return moves;}
    function findKing(b,color){for(var r=0;r<8;r++)for(var c=0;c<8;c++)if(b[r][c]===color+'k')return{r:r,c:c};return null;}
    function isAttacked(b,r,c,byColor){for(var rr=0;rr<8;rr++)for(var cc=0;cc<8;cc++){var p=b[rr][cc];if(p&&p[0]===byColor){var mvs=pseudoMoves(b,rr,cc);for(var i=0;i<mvs.length;i++){if(mvs[i].r===r&&mvs[i].c===c)return true;}}}return false;}
    function legalMoves(b,r,c){var piece=b[r][c];if(!piece)return[];var color=piece[0];return pseudoMoves(b,r,c).filter(function(m){var copy=b.map(function(row){return row.slice();});copy[m.r][m.c]=copy[r][c];copy[r][c]=null;var kp=piece[1]==='k'?{r:m.r,c:m.c}:findKing(copy,color);return!isAttacked(copy,kp.r,kp.c,color==='w'?'b':'w');});}
    function allLegalMoves(b,color){var all=[];for(var r=0;r<8;r++)for(var c=0;c<8;c++){if(b[r][c]&&b[r][c][0]===color){var mvs=legalMoves(b,r,c);mvs.forEach(function(m){all.push({from:{r:r,c:c},to:m});});}}return all;}
    function addEnPassant(r,c,moves){var piece=board[r][c];if(!piece||piece[1]!=='p'||!epTarget)return moves;var color=piece[0],dir=color==='w'?-1:1;if(r+dir===epTarget.r&&Math.abs(c-epTarget.c)===1){var capSquare={r:r,c:epTarget.c};var copy=board.map(function(row){return row.slice();});copy[epTarget.r][epTarget.c]=copy[r][c];copy[r][c]=null;copy[capSquare.r][capSquare.c]=null;var kp=findKing(copy,color);if(!isAttacked(copy,kp.r,kp.c,color==='w'?'b':'w'))moves=moves.concat([{r:epTarget.r,c:epTarget.c,capture:true,enPassant:true,capSquare:capSquare}]);}return moves;}
    function getCastleMoves(color){var moves=[],row=color==='w'?7:0;if(board[row][4]!==color+'k')return moves;var enemy=color==='w'?'b':'w';if(isAttacked(board,row,4,enemy))return moves;if(castling[color].K&&!board[row][5]&&!board[row][6]&&board[row][7]===color+'r'){if(!isAttacked(board,row,5,enemy)&&!isAttacked(board,row,6,enemy))moves.push({r:row,c:6,castle:'K'});}if(castling[color].Q&&!board[row][1]&&!board[row][2]&&!board[row][3]&&board[row][0]===color+'r'){if(!isAttacked(board,row,3,enemy)&&!isAttacked(board,row,2,enemy))moves.push({r:row,c:2,castle:'Q'});}return moves;}
    function realLegalMoves(r,c){var moves=legalMoves(board,r,c).slice();var piece=board[r][c];if(piece&&piece[1]==='p')moves=addEnPassant(r,c,moves);if(piece&&piece[1]==='k')moves=moves.concat(getCastleMoves(piece[0]));return moves;}
    function realAllLegalMoves(color){var all=[];for(var r=0;r<8;r++)for(var c=0;c<8;c++){if(board[r][c]&&board[r][c][0]===color)realLegalMoves(r,c).forEach(function(m){all.push({from:{r:r,c:c},to:m});});}return all;}
    function render(){boardEl.innerHTML='';for(var r=0;r<8;r++){for(var c=0;c<8;c++){var sq=document.createElement('div');sq.className='chess-sq '+(((r+c)%2===0)?'light':'dark');var piece=board[r][c];if(piece){var span=document.createElement('span');span.className='chess-piece';span.innerHTML=pieceMarkup(piece);sq.appendChild(span);}if(selected&&selected.r===r&&selected.c===c)sq.classList.add('selected');var isTarget=false;for(var ti=0;ti<legalTargets.length;ti++){if(legalTargets[ti].r===r&&legalTargets[ti].c===c){isTarget=true;break;}}if(isTarget)sq.classList.add('valid-target');if(lastMove&&((lastMove.from.r===r&&lastMove.from.c===c)||(lastMove.to.r===r&&lastMove.to.c===c)))sq.classList.add('last-move');(function(rr,cc){sq.addEventListener('click',function(){handleClick(rr,cc);});})(r,c);boardEl.appendChild(sq);}}}
    function updateStatus(){var moves=allLegalMoves(board,turn),kp=findKing(board,turn),inCheck=isAttacked(board,kp.r,kp.c,turn==='w'?'b':'w');if(moves.length===0){over=true;statusEl.textContent=inCheck?((turn===humanColor?'Computer wins by checkmate!':'You win by checkmate!')):'Stalemate — draw';return true;}statusEl.textContent=(turn===humanColor?'Your turn':"Computer's turn")+(inCheck?' — check!':'');return false;}
    function updateCastlingRights(from,movedPiece,capturedSquare,capturedPiece){var color=movedPiece[0];if(movedPiece[1]==='k'){castling[color].K=false;castling[color].Q=false;}if(movedPiece[1]==='r'){if(color==='w'){if(from.r===7&&from.c===0)castling.w.Q=false;if(from.r===7&&from.c===7)castling.w.K=false;}else{if(from.r===0&&from.c===0)castling.b.Q=false;if(from.r===0&&from.c===7)castling.b.K=false;}}if(capturedPiece&&capturedPiece[1]==='r'){var cc=capturedPiece[0];if(cc==='w'){if(capturedSquare.r===7&&capturedSquare.c===0)castling.w.Q=false;if(capturedSquare.r===7&&capturedSquare.c===7)castling.w.K=false;}else{if(capturedSquare.r===0&&capturedSquare.c===0)castling.b.Q=false;if(capturedSquare.r===0&&capturedSquare.c===7)castling.b.K=false;}}}
    function simulateMove(b,from,move){var copy=b.map(function(row){return row.slice();});var piece=copy[from.r][from.c];copy[move.r][move.c]=piece;copy[from.r][from.c]=null;if(move.enPassant&&move.capSquare)copy[move.capSquare.r][move.capSquare.c]=null;if(move.castle==='K'){var row=from.r;copy[row][5]=copy[row][7];copy[row][7]=null;}else if(move.castle==='Q'){var row2=from.r;copy[row2][3]=copy[row2][0];copy[row2][0]=null;}if(piece[1]==='p'&&(move.r===0||move.r===7))copy[move.r][move.c]=piece[0]+'q';return copy;}
    function makeMove(from,move){var piece=board[from.r][from.c],captured=board[move.r][move.c];board[move.r][move.c]=piece;board[from.r][from.c]=null;if(move.enPassant&&move.capSquare)board[move.capSquare.r][move.capSquare.c]=null;if(move.castle==='K'){var row=from.r;board[row][5]=board[row][7];board[row][7]=null;}else if(move.castle==='Q'){var row2=from.r;board[row2][3]=board[row2][0];board[row2][0]=null;}if(piece[1]==='p'&&(move.r===0||move.r===7))board[move.r][move.c]=piece[0]+'q';updateCastlingRights(from,piece,{r:move.r,c:move.c},captured);epTarget=null;if(piece[1]==='p'&&Math.abs(move.r-from.r)===2)epTarget={r:(from.r+move.r)/2,c:from.c};lastMove={from:{r:from.r,c:from.c},to:{r:move.r,c:move.c}};}
    function handleClick(r,c){if(over||thinking||turn!==humanColor)return;var piece=board[r][c];if(selected){var target=null;for(var i=0;i<legalTargets.length;i++){if(legalTargets[i].r===r&&legalTargets[i].c===c){target=legalTargets[i];break;}}if(target){makeMove(selected,target);selected=null;legalTargets=[];turn=aiColor;var done=updateStatus();render();if(!done)aiTurn();return;}if(piece&&piece[0]===turn){selected={r:r,c:c};legalTargets=realLegalMoves(r,c);render();return;}selected=null;legalTargets=[];render();return;}else if(piece&&piece[0]===turn){selected={r:r,c:c};legalTargets=realLegalMoves(r,c);render();}}
    var VALUE={p:1,n:3,b:3.1,r:5,q:9,k:0};
    function evaluateBoard(b){var score=0;for(var r=0;r<8;r++)for(var c=0;c<8;c++){var p=b[r][c];if(p)score+=(p[0]==='w'?1:-1)*VALUE[p[1]];}return score;}
    function search(b,color,depth){var moves=allLegalMoves(b,color);if(moves.length===0){var kp=findKing(b,color),inCheck=isAttacked(b,kp.r,kp.c,color==='w'?'b':'w');if(!inCheck)return 0;return color==='w'?-999:999;}if(depth===0)return evaluateBoard(b);var best=color==='w'?-Infinity:Infinity;for(var i=0;i<moves.length;i++){var mv=moves[i],copy=b.map(function(row){return row.slice();});copy[mv.to.r][mv.to.c]=copy[mv.from.r][mv.from.c];copy[mv.from.r][mv.from.c]=null;if(copy[mv.to.r][mv.to.c][1]==='p'&&(mv.to.r===0||mv.to.r===7))copy[mv.to.r][mv.to.c]=copy[mv.to.r][mv.to.c][0]+'q';var val=search(copy,color==='w'?'b':'w',depth-1);if(color==='w'){if(val>best)best=val;}else{if(val<best)best=val;}}return best;}
    function aiTurn(){if(over||turn!==aiColor)return;thinking=true;statusEl.textContent="Computer is thinking…";timeoutId=setTimeout(function(){var moves=realAllLegalMoves(aiColor);if(moves.length===0){thinking=false;return;}var bestVal=aiColor==='w'?-Infinity:Infinity,bestMoves=[];for(var i=0;i<moves.length;i++){var mv=moves[i],copy=simulateMove(board,mv.from,mv.to),val=search(copy,humanColor,2);if(aiColor==='w'){if(val>bestVal+0.001){bestVal=val;bestMoves=[mv];}else if(Math.abs(val-bestVal)<=0.001){bestMoves.push(mv);}}else{if(val<bestVal-0.001){bestVal=val;bestMoves=[mv];}else if(Math.abs(val-bestVal)<=0.001){bestMoves.push(mv);}}}var chosen=bestMoves[Math.floor(Math.random()*bestMoves.length)];makeMove(chosen.from,chosen.to);thinking=false;turn=humanColor;updateStatus();render();},400);}
    showPicker();
    return function(){clearTimeout(timeoutId);};
  }
});
