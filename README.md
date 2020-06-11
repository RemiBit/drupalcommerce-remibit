DRUPAL COMMERCE REMIBIT MODULE 

## Integration Requirements

- A RemiBit merchant account.
- Drupal Commerce, tested with drupal version up to 8.9.0 and commerce up to 2.19

INSTALLATION AND CONFIGURATION


In order to install the module, it is necessary to access the server where the web files are hosted by ssh. If you don’t know how to do that, please contact your site administrator or your hosting provider.

In this example we will use the default Drupal Commerce configuration, so the website files are located in /var/www/html/mystore and they are owned by the default user www-data. Please replace [mystore] with the actual name (if different) of your website directory and [www-data] with the owner (if different) of your web files directory.

1/. Go to the drupal directory and then to the directory where you have to place the module files.

```
cd /var/www/html/mystore
cd web/modules/custom
```

2/. Fetch the RemiBit module

```
sudo -u www-data wget https://github.com/RemiBit/drupalcommerce-remibit/releases/download/v1.01/drupalcommerce-remibit.zip
```

3/. Uncompress and install it

```
sudo -u www-data unzip drupalcommerce-remibit.zip

sudo -u www-data drupal moi remibit
```


4/. The RemiBit module is now installed and activated. To verify that, please go to your drupal site administration panel. 

Click on Extend and scroll down to COMMERCE (CONTRIB)

RemiBit Payment Method is ticked, which means it’s already activated.


5/. Now go to Commerce > Configuration > Payment > Payment gateways

Click on Add payment gateway

Select ``REMIBIT Payment Method``

Click on ``Name`` and write ``RemiBit``

6/. Fill up ``Login ID``, ``Transaction Key``, ``Signature Key`` and ``MD5 Hash Value`` with the data you get from your RemiBit account’s Settings > Gateway

Click on Save and you’re done.

