# Reshma Beauty Parlour - client website

## Folders

    clientSite/
      index.html       home page
      pages/           every other screen
      css/             one stylesheet per screen, plus two shared ones
      js/
        data/          the lists and details the pages are built from
        components/    the repeated pieces, written once
        <page>.js      one script per screen
      images/          your pictures go here 


## Components

| Component        | What it builds                                         | Used on                  |
|------------------|--------------------------------------------------------|--------------------------|
| picture.js       | every picture, with a coloured box if the file is missing | every page            |
| siteHeader.js    | header, logo, navigation, cart number, mobile menu     | every page               |
| siteFooter.js    | footer                                                 | every page               |
| contactLinks.js  | phone, WhatsApp, email and social links                | footer, about            |
| messageModal.js  | showMessage() and askToConfirm() pop-ups               | every page               |
| formField.js     | form checking, error styling, code generation          | forms                    |
| filterChips.js   | the round filter buttons                               | services, shop           |
| categoryCard.js  | one service group card                                 | home                     |
| serviceCard.js   | one service card                                       | services                 |
| productCard.js   | one product card                                       | home, shop               |
| cartLine.js      | one line of the cart                                   | cart                     |
| orderLines.js    | summary rows and order items                           | checkout, payment, track |
| statusTag.js     | the coloured status label                              | payment, track           |
| openingHours.js  | the opening hours table                                | about                    |
| questionItem.js  | one open-and-close question                            | about                    |

## Data

`js/data/` holds everything you are likely to change:

| File             | What is in it                                              |
|------------------|------------------------------------------------------------|
| businessInfo.js  | name, phone, WhatsApp, email, address, hours, Juice, fees  |
| siteImages.js    | logo, home banner, about picture                           |
| services.js      | service groups (with their home page pictures) and services|
| products.js      | product groups and products (with their photos and sizes)  |
| cartItems.js     | example cart                                               |
| orderExamples.js | example order and booking for the payment and track pages  |
| statusLabels.js  | the words and colours of every status                      |
| faqList.js       | the questions on the about page                            |

## Pictures

No picture files are included except the logo. Every picture path is
written in a data file, from the top of clientSite:

    images/banners/homeBanner.jpg
    images/about/aboutSalon.jpg
    images/categories/hairServices.jpg   nailServices.jpg   otherServices.jpg
    images/services/<service id>.jpg     e.g. cutAndBlowDry.jpg
    images/products/<product id><n>.jpg  e.g. floralSummerDress1.jpg

Save a picture with that exact name and it appears. Or keep your own file
name and change the path in the data file. Using .png or .webp? Change the
end of the path too. Until a file exists, a soft coloured box with the
picture's name is shown instead, so nothing looks broken.


