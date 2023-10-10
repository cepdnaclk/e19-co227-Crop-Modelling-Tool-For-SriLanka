<!DOCTYPE html>
<html>
<head>
    <title>Graph Page</title>
    <!-- Include Plotly.js library -->
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
    <style>
        body {
            font-family: poppins;
            padding: 0;
            margin: 0;
            background: url('images/banner2.jpg') no-repeat center center fixed; /* Replace 'agriculture-background.jpg' with your background image */
            background-size: cover; /* Ensures the background covers the entire page */
            color: white;

            /* Add background color with opacity */
            background-color: rgba(5, 37, 1, 0.1); /* Adjust the last value (0.5) for opacity (0 to 1) */
        }
        .header {
            font-family: poppins;
            padding: 20px 0px; /* Increased top and bottom padding */
            background: #052501;
            background-size: cover; /* Ensures the background covers the entire header */
            color: white;
            text-align: center; /* Center align text */
        }

        .header h1 {
            font-size: 44px; /* Increase font size */
            margin-bottom: 10px; /* Add space below the heading */            
            color: #2bd598;
        }

        .subtitle {
            font-size: 20px; /* Font size for the subtitle */
            color: green;
        }

        /* Container for the dropdown and back button */
        .dropdown-container {
            display: flex;
            justify-content: flex-start; /* Align items to the left */
            align-items: center;
            padding: 10px;
            margin-left: 10px; /* Add left margin to move the container to the left */
            margin-right: 10px; /* Add right margin to center-align the container */
        }

        /* Dropdown */
        #select-data {
            background-color: #197f5a; /* Dark green */
            color: #fff; /* White text */
            border: none;
            border-radius: 5px;
            padding: 10px;
            cursor: pointer;
            margin-right: 10px;
        }

        /* Dropdown options */
        #select-data option {
            background-color: #197f5a; /* Dark green */
            color: #fff; /* White text */
        }

        /* Hover effect for dropdown options */
        #select-data option:hover {
            background-color: #23644e; /* Dark teal */
        }

        /* Back button */
        .back-button {
            background-color: #197f5a; /* Dark green */
            color: #fff; /* White text */
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
            margin-left: auto; /* Push the button to the right */
        }

        /* Hover effect for back button */
        .back-button:hover {
            background-color: #23644e; /* Dark teal */
        }

        /* Footer */
        footer {
            background-color: #2bd598; /* Teal */
            color: #fff; /* White text */
            text-align: center;
            padding: 10px;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="container">
            <h1>CROPLANKA</h1>
            <div class="subtitle">D A T A  &nbsp &nbsp V I S U A L I Z E R</div>
        </div>
    </div>

    <br>
    <!-- Container for dropdown and back button -->
    <div class="dropdown-container">
        <!-- This is for choosing the type of graph -->
        <label for="select-data">SELECT GRAPH TYPE :&nbsp </label>
        <select id="select-data">
            <option value="Scatter" selected>SCATTER</option>
            <option value="Box">BOX</option>
            <option value="Cumulative Distribution">CUMULATIVE DISTRIBUTION</option>
            <option value="Probability Exceedance">PROBABILITY EXCEEDANCE</option>
            <!-- Add more options as needed -->
        </select>
        
        <!-- Back button -->
        <button class="back-button" onclick="goBack()">Back</button>
    </div>

    <script>
        // JavaScript code here
        function goBack() {
            window.history.back();
        }
    </script>

    <br> 

    <?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "227project";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $database);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } else {
        // Sanitize user input (if needed)
        if (isset($_GET['resultTexts'])) {
            $resultTextsJSON = $_GET['resultTexts'];
            $selections = json_decode($resultTextsJSON, true); // Decode JSON into an array
            // Now you can use the $selections array in your PHP code
        }
        foreach ($selections as $selection) {
            echo "&nbsp &nbsp &nbsp &#x2022 ";
            echo ucfirst($selection);
            echo "<br>";
        }

        $tableName = "output"; 
        // Fetch data from the database
        $sql = "SELECT * FROM $tableName";
        $result = $conn->query($sql);

        if ($result) {
            $data = array();
            $key = array();
            $cumulative = array();
            $probability = array();

            // Collect data for plotting
            while ($row = $result->fetch_assoc()) {
                foreach ($selections as $selection) {
                    if ($row['Key'] == $selection) {

                        $key[] = $row['Key'];
                        $data[] = $row['Rice_yield'];
                        $probability[] = $row['Probability'];
                        $cumulative[] = $row['Cumulative'];

                    }
                }
            }
        } else {
            echo "Error: " . $conn->error;
        }
    }

    $conn->close();
    ?>
    
    <!-- Add a div element to hold the scatter plot -->
    <div id="chart"></div>
    <script>
        // Get the x and y arrays from PHP
        var xData = <?php echo json_encode($key); ?>;
        var yData = <?php echo json_encode($data); ?>;
        var y2Data = <?php echo json_encode($probability); ?>;
        var y3Data = <?php echo json_encode($cumulative); ?>;

        // Event listener for the select element
        document.getElementById('select-data').addEventListener('change', function () {
            var selectedValue = this.value;

            // Determine which chart type to create
            if (selectedValue === 'Scatter') {
                createScatterPlot(xData, yData);
            } else if (selectedValue === 'Box') {
                createBoxPlot(xData, yData);
            }
            else if (selectedValue === 'Probability Exceedance') {
                createProbabilityPlot(yData, y2Data,xData);
            }else if (selectedValue === 'Cumulative Distribution') {
                createCumulativePlot(yData, y3Data,xData);
            }
            // Add more conditions for other chart types as needed
        });
        // Set default selected option on page load
        document.addEventListener('DOMContentLoaded', function () {
            var defaultSelectedValue = 'Scatter';
            document.getElementById('select-data').value = defaultSelectedValue;

            // Initialize the chart based on the default selection (scatter plot)
            createScatterPlot(xData, yData);
        });

        // Initialize the chart based on the default selection (scatter plot)
        //createScatterPlot(xData, yData);


        function formatXData(x) {
            return x.substring(0, 2) + ',' + x.substring(2, 8) + ',' + x.substring(8, 10) + ',' + x.substring(10, 12) + ',' + x.substring(12, 14) + ',' + x.substring(14, 16);
        }

function createScatterPlot(xData, yData) {
    // Format x-axis data
    var formattedXData = xData.map(formatXData);

    // Create trace
    var trace = {
        x: formattedXData,
        y: yData,
        mode: 'markers',
        type: 'scatter',
        name: 'Scatter plot',
        marker: {
            color: '#157954', // Green color for data points
            size: 8, // Adjust the size of markers
        },
    };

            var layout = {
                title: 'SCATTER PLOT',
                xaxis: { title: 'Scenarios' },
                yaxis: { title: 'Yield-Values'},
                paper_bgcolor: 'rgb(233, 233, 233)',
                plot_bgcolor: 'rgb(233, 233, 233)',
            };

            // Create the scatter plot inside the 'chart' div
            Plotly.newPlot('chart', [trace], layout);
        }

        // Function to create a box plot using Plotly with an agriculture theme
        function createBoxPlot(xData, yData) {
            var formattedXData = xData.map(formatXData);

            var trace = {
                x: formattedXData,
                y: yData,
                type: 'box',
                name: 'Box Plot',
                marker: {
                    color: '#157954', // Green color for the box plot
                }
            };

            var layout = {
                title: 'BOX PLOT',
                xaxis: { title: 'Scenarios' },
                yaxis: { title: 'Yield-Values' },
                paper_bgcolor: 'rgb(233, 233, 233)',
                plot_bgcolor: 'rgb(233, 233, 233)',
            };

            // Create the box plot inside the 'chart' div
            Plotly.newPlot('chart', [trace], layout);
        }

        function createProbabilityPlot(xData, yData,colordata) {
    var traces = [];
    var currentTrace = {
        x: [],
        y: [],
        type: 'scatter',
        mode: 'lines+markers',
        name: colordata[0],
        marker: {
            color: getRandomColor(), // Initial color for the cumulative plot
        },
    };

    for (var i = 0; i < xData.length; i++) {
        currentTrace.x.push(xData[i]);
        currentTrace.y.push(yData[i]);

        // Check if the next y-value is smaller, indicating a new line
        if (!(colordata[i + 1] == colordata[i]) ) {
            traces.push(currentTrace);

            currentTrace = {
                x: [],
                y: [],
                type: 'scatter',
                mode: 'lines+markers',
                name: colordata[i+1],
                marker: {
                    color: getRandomColor(), // Assign a new color for the new line segment
                },
            };
        }
    }

    // Push the last trace
    traces.push(currentTrace);

    var layout = {
        title: 'PROBABILITY EXCEDENCE',
        xaxis: { title: 'Yield-Values' },
        yaxis: { title: 'Probability' },
        paper_bgcolor: 'rgb(233, 233, 233)',
        plot_bgcolor: 'rgb(233, 233, 233)',
        legend: { font: { size: 16 } }
    };

    // Create the cumulative plot inside the 'chart' div
    Plotly.newPlot('chart', traces, layout);
}


        function createCumulativePlot(xData, yData,colordata) {
    var traces = [];
    var currentTrace = {
        x: [],
        y: [],
        type: 'scatter',
        mode: 'lines+markers',
        name: colordata[0],
        marker: {
            color: getRandomColor(), // Initial color for the cumulative plot
        },
    };

    for (var i = 0; i < xData.length; i++) {
        currentTrace.x.push(xData[i]);
        currentTrace.y.push(yData[i]);

        // Check if the next y-value is smaller, indicating a new line
        if (colordata[i+1] != colordata[i]) {
            traces.push(currentTrace);

            currentTrace = {
                x: [],
                y: [],
                type: 'scatter',
                mode: 'lines+markers',
                name: colordata[i+1],
                marker: {
                    color: getRandomColor(), // Assign a new color for the new line segment
                },
            };
        }
    }

    // Push the last trace
    traces.push(currentTrace);

    var layout = {
        title: 'CUMULATIVE DISTRIBUTION',
        xaxis: { title: 'Yield-Values' },
        yaxis: { title: 'Cumulative Values' },
        paper_bgcolor: 'rgb(233, 233, 233)',
        plot_bgcolor: 'rgb(233, 233, 233)',
        legend: { font: { size: 16 } }
    };

    // Create the cumulative plot inside the 'chart' div
    Plotly.newPlot('chart', traces, layout);
}


// Function to generate a random color
function getRandomColor() {
    var letters = '0123456789ABCDEF';
    var color = '#';
    for (var i = 0; i < 6; i++) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
}


    </script>
</body>
</html>
