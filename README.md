<!-- PROJECT LOGO -->
<br />
<p align="center">
  <a href="https://github.com/caffeinalab/postino">
    <img src="res/postman.png" alt="Logo" width="130" height="130">
  </a>
  <h1 align="center">POSTINO</h1>

  <p align="center">
    A new breath of life to wp_mail.
  </p>
</p>



<!-- TABLE OF CONTENTS -->
## Table of Contents

- [Table of Contents](#table-of-contents)
- [About The Project](#about-the-project)
  - [How it works](#how-it-works)
- [Getting Started](#getting-started)
  - [Updates](#updates)
- [Usage](#usage)
- [Contributing](#contributing)
- [License](#license)
- [Contact](#contact)



<!-- ABOUT THE PROJECT -->
## About The Project
![Product Name Screen Shot][screenshot]


**Postino** was built to make sending emails via WP the easiest it can get. It provides a setting page where you can set the SMTP parameters, but, in case of batch installations, you can even put a configuration file in your theme. 
### How it works
The plugin simply uses the WordPress hook `phpmailer_ini` to set the SMTP configuration. 

> The versions previous to 1.0.5 used to override the `wp_mail` which both lead to the introduction of bugs - such as the email not being formatted correctly - and was an overkill for just setting the SMTP configuration.

<!-- GETTING STARTED -->
## Getting Started

You can just clone this repository inside your `wp-content/plugins` folder, or [download the installable zip](https://github.com/caffeinalab/postino/releases/latest/download/postino.zip) and install it via the WordPress dashboard. 

### Updates
Since the release 1.0.1, you can update Postino directly from the WordPress' dashboard, like any other plugin.


<!-- USAGE EXAMPLES -->
## Usage

To use Postino, just install it and set up your SMTP server in the settings page. Don't want to do that? Just include a `postino.json` file in your active theme folder. Postino will look for it and load the settings in it. A `postino.json` file defines these settings:

```json
{
    "smtp_secure": "ssl",
    "smtp_port": 465,
    "smtp_server": "smtp.gmail.com",
    "smtp_user": "sim.montali@gmail.com",
    "smtp_password": "realpassword!",
    "mail_sender": "sim.montali@gmail.com",
    "mail_sender_name": "Simone M."
}
```

Everything is now set up. You don't need to worry about anything else.



<!-- CONTRIBUTING -->
## Contributing

Contributions are what make the open source community such an amazing place to be learn, inspire, and create. Any contributions you make are **greatly appreciated**.

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request



<!-- LICENSE -->
## License

Distributed under the GPL License. See `LICENSE` for more information.
<div>Icon made by <a href="https://www.flaticon.com/authors/freepik" title="Freepik">Freepik</a> from <a href="https://www.flaticon.com/"             title="Flaticon">www.flaticon.com</a></div>


<!-- CONTACT -->
## Contact

[Simone Montali](https://monta.li) - [@Caffeina](https://caffeina.com)

Project Link: [https://github.com/caffeinalab/postino](https://github.com/simmontali/postino)

[screenshot]: res/screenshot.gif "Screenshot"
[logo]: res/postman.png
