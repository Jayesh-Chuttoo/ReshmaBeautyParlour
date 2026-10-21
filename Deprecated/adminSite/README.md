# Reshma Beauty Parlour - admin website

A separate website for the salon. Clients never see it.
It opens on the sign-in page (index.html); every other screen is in pages/.

## Signing in (for now)

The email and password are in `js/data/adminAccount.js`. Change them there.

Password rules, checked on the Sign-in details page and by the server later:
at least 1 capital letter, 1 small letter, 1 number and 1 symbol,
and 8 characters at most.



## Screens

| Screen               | File                       | What it does                          |
|----------------------|----------------------------|---------------------------------------|
| Sign in              | index.html                 | email and password                    |
| Stock                | pages/adminInventory.html  | stock numbers, add and edit products  |
| Orders               | pages/adminOrders.html     | confirm payments, change status       |
| Bookings             | pages/adminBookings.html   | confirm times and agreed prices       |
| Pictures             | pages/adminImages.html     | replace any picture on the website    |
| Sign-in details      | pages/adminAccount.html    | change the email and password         |

## Components

| Component          | What it builds                                        |
|--------------------|-------------------------------------------------------|
| adminShell.js      | top bar and side menu                                 |
| messageModal.js    | showMessage() and askToConfirm() pop-ups              |
| formField.js       | form checking, checkPictureFile()                     |
| passwordRules.js   | the password rules and the ticking checklist          |
| picture.js         | a picture from the client site, or a coloured box     |
| imageSlotCard.js   | one card on the Pictures page                         |
| statusTag.js       | status labels and status filter choices               |
| tableFilter.js     | search and filters (tables and picture cards)         |
| stockRow.js        | one row of the stock table                            |
| orderRow.js        | one row of the orders table                           |
| bookingRow.js      | one row of the bookings table                         |

## Data

| File              | What is in it                                               |
|-------------------|-------------------------------------------------------------|
| adminAccount.js   | sign-in email and password (for now only)                   |
| adminSettings.js  | client site address, low-stock level, picture limits        |
| adminData.js      | example stock, orders, bookings, and the status words       |
| imageSlots.js     | every picture on the client site, for the Pictures page     |

`adminSettings.clientSiteAddress` is "../../clientSite/" while both folders
sit side by side. Once the client site is online, put its real address.
