<h2> About This Project </h2>

The aim of this project is to demonstrate how automation can be used to pull data from an API which can be found on mashape (IGDB) and automatically create posts on Wordpress.

There are two types of php script files:
<ol>
<li> To search for the latest game releases and,</li>
<li> To search for a game by title.</li>
</ol>
Due to network speed lag only the thumbnail image is downloaded to Wordpress images. Before the thumbnail image is downloaded it is transformed to the appropriate resolution and quality.
For the image to be transformed an additional API is required, called cloudinary. This API allows you to upload your own images or use and modify other available images.Apart from scaling and adjusting the resolution, cloudinary is also capable of adding filters such as blur and distortion, etc.

Here are the API used in this projects.

<li><a href="https://market.mashape.com/igdbcom/internet-game-database/">IGDB API</a></li>
<li><a href="https://cloudinary.com/">Cloudinary API</a></li>


After creating the accounts make sure that you will update the Project with your API keys.
