docker compose stop &&
docker compose down -v &&
docker volume rm -f ledyer-checkout-for-magento2_mgdata &&
docker volume rm -f ledyer-checkout-for-magento2_dbdata &&
docker volume rm -f ledyer-checkout-for-magento2_esdata &&
docker compose rm
