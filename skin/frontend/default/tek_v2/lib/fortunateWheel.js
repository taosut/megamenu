var $j = jQuery.noConflict();
$j(document).ready(function(){

    var theWheel = new Winwheel({
        'canvasID' : 'myCanvas',
        'numSegments' : 24,
        'rotationAngle' : -22.5,
        'drawMode' : 'image',
        'segments'    :
            [
                {'text' : '1'},
                {'text' : '2'},
                {'text' : '3'},
                {'text' : '4'},
                {'text' : '5'},
                {'text' : '6'},
                {'text' : '7'},
                {'text' : '8'},
                {'text' : '9'},
                {'text' : '10'},
                {'text' : '11'},
                {'text' : '12'},
                {'text' : '13'},
                {'text' : '14'},
                {'text' : '15'},
                {'text' : '16'},
                {'text' : '17'},
                {'text' : '18'},
                {'text' : '19'},
                {'text' : '20'},
                {'text' : '21'},
                {'text' : '22'},
                {'text' : '23'},
                {'text' : '24'}
            ],
        'pointerAngle' : 270,
        'lineWidth' : 1,
        'outerRadius' : 327,
        'innerRadius' : 50,
        'lineWidth'   : 3,
        'pointerGuide' :
            {
                'display' : false,
                'strokeStyle' : 'red',
                'lineWidth' : 3
            },
        'animation' :
            {
                'type' : 'spinToStop',
                'duration' : 12,
                'spins'    : 6,
                'callbackFinished' : 'alertPrize()',
                'easing'       : 'Power4.easeOut',
                'yoyo'         : true,
            }
    });
    loadedImg = new Image();
    loadedImg.onload = function()
    {
        theWheel.wheelImage = loadedImg;
        theWheel.draw();
    }

    loadedImg.src="Keo_Go.png";

    function alertPrice()
    {
        var winningSegment = theWheel.getIndicatedSegment();
        alert("Chào Lợi " + winningSegment.text + "!");
    }

    function calculatePrize()
    {
        var stopAt =325;
        theWheel.animation.stopAngle = stopAt;
        theWheel.startAnimation();
    }

    function winAnimation()
    {
        var winningSegmentNumber = theWheel.getIndicatedSegmentNumber();
        for(var i = 1;i < theWheel.segments.length; i++){
            if(i != winningSegmentNumber){
                theWheel.segments[i].fillStyle ='gray';
            }else{
                theWheel.segments[i].fillStyle ='yellow';
            }
        }
        theWheel.draw();

    }

    c = theWheel.ctx;

    if(c)
    {
        c.save();
        c.lineWidth = 2;
        c.strokeStyle = 'black';
        c.fillStyle = 'black';
        c.beginPath();
        c.moveTo(180,10);
        c.lineTo(220,10);
        c.lineTo(200,42);
        c.lineTo(180,10);
        c.stroke();
        c.fill();
        c.restore();
    }

    function changeColours(){
        var temp = theWheel.segments[1].fillStyle;
        var tempText = theWheel.segments[1].text;
        theWheel.segments[1].fillStyle = theWheel.segments[2].fillStyle;
        theWheel.segments[1].text = theWheel.segments[2].text;
        theWheel.segments[2].fillStyle = theWheel.segments[3].fillStyle;
        theWheel.segments[2].text = theWheel.segments[3].text;
        theWheel.segments[3].fillStyle = theWheel.segments[4].fillStyle;
        theWheel.segments[3].text = theWheel.segments[4].text;
        theWheel.segments[4].fillStyle = temp;
        theWheel.segments[4].text = tempText;
        theWheel.draw();
    }

});