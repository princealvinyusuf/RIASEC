<!-- js code or links will come here -->
<?php if (!isset($scorePercentageList) || !is_array($scorePercentageList)) { $scorePercentageList = array('R'=>0,'I'=>0,'A'=>0,'S'=>0,'E'=>0,'C'=>0); } ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
<script type="text/javascript">
window.onload = function () {
    var container = document.getElementById('chartContainer');
    if (!container) { return; }
    var chart = new CanvasJS.Chart("chartContainer", {
        title:{
            text: "RIASEC test results in percentages"              
        },
        data: [              
        {
            type: "column",
            dataPoints: [
                { label: "Realistic", 
                 y: <?php echo $scorePercentageList['R']?>  },
                 
                { label: "Investigative",
                 y: <?php echo $scorePercentageList['I']?>  },
                
                { label: "Artistic",
                 y: <?php echo $scorePercentageList['A']?>  },
                 
                { label: "Social", 
                 y: <?php echo $scorePercentageList['S']?>  },
                
                { label: "Enterprising", 
                 y: <?php echo $scorePercentageList['E']?>  },
                 
                { label: "Conventional", 
                 y: <?php echo $scorePercentageList['C']?> }
            ]
        }
        ]
    });
    chart.render();
}
</script>
</body>
</html>