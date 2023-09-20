<!DOCTYPE html>
<html>
<head>
    <title>Graph page</title>
    <!-- Include Plotly.js library -->
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
</head>
<body>
    <h1>My Plot</h1>

    <!-- This is for to choose what graph that you want -->
    <label for="select-data">Select Data:</label>
    <select id="select-data">
        <option value="Scatter">Scatter</option>
        <option value="Box">Box</option>
        <option value="Cumulative Distribution">Cumulative Distribution</option>
        <option value="robability Exceedance">Probability Exceedance</option>
        <!-- Add more options as needed -->
    </select>
    <br>

    <?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "project";

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
            // Now you can use the $resultTexts array in your PHP code
        }
        foreach($selections as$selection){
            echo "&nbsp &nbsp &nbsp &#x2022 ";
            echo ucfirst($selection) ;
            echo "<br>";
        }

        $tableName = "output"; 
        // Fetch data from the database
        $sql = "SELECT * FROM $tableName";
        $result = $conn->query($sql);

        if ($result) {
            $data = array();
            $key = array();
            
        // Collect data for plotting
            while ($row = $result->fetch_assoc()) {
                foreach($selections as$selection){
                    if ($row['Key']==$selection){
                
                        $key[] = $row['Key'];
                        $data[] = $row['Yield Date'];
                    }
                }
            }
        }else {
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

        // Event listener for the select element
        document.getElementById('select-data').addEventListener('change', function () {
            var selectedValue = this.value;

            // Determine which chart type to create
            if (selectedValue === 'Scatter') {
                createScatterPlot(xData, yData);
            } else if (selectedValue === 'Box') {
                createBoxPlot(xData, yData);
            }
            // Add more conditions for other chart types as needed
        });

        // Initialize the chart based on the default selection (scatter plot)
        createScatterPlot(xData, yData);

        // Function to create a scatter plot using Plotly
        function createScatterPlot(xData, yData) {
            var trace = {
                x: xData,
                y: yData,
                mode: 'markers',
                type: 'scatter',
                name: 'Scatter Plot',
            };

            var layout = {
                title: 'Scatter Plot',
                xaxis: { title: 'X-Axis' },
                yaxis: { title: 'Y-Axis' },
                paper_bgcolor: 'rgb(233, 233, 233)',
                plot_bgcolor: 'rgb(233, 233, 233)',
            };

            // Create the scatter plot inside the 'chart' div
            Plotly.newPlot('chart', [trace], layout);
        }

        // Function to create a box plot using Plotly
        function createBoxPlot(xData, yData) {
            var trace = {
                x: xData,
                y: yData,
                type: 'box',
                name: 'Box Plot',
                marker: {
                    color: 'RGB(86, 168, 50)' // Change color
                }
            };

            var layout = {
                title: 'Box Plot',
                xaxis: { title: 'X-Axis' },
                yaxis: { title: 'Y-Axis' },
                paper_bgcolor: 'rgb(233, 233, 233)',
                plot_bgcolor: 'rgb(233, 233, 233)',
            };

            // Create the box plot inside the 'chart' div
            Plotly.newPlot('chart', [trace], layout);
        }
    </script>

</body>
</html>




