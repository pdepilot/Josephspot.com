# Real-Time Analytics System Setup Guide

This guide will help you set up the complete PHP/MySQL analytics system for your website.

## Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Database access credentials

## Installation Steps

### 1. Create Database Table

Run the SQL schema file to create the `page_views` table:

```bash
mysql -u your_username -p your_database_name < analytics/schema.sql
```

Or manually execute the SQL in `analytics/schema.sql` using phpMyAdmin or your preferred MySQL client.

### 2. Verify Database Connection

Ensure your `db_connection.php` file has the correct database credentials:
- Database name: `joseph_pot_admin`
- Username and password as configured

### 3. File Structure

The following files have been created:

```
analytics/
  ├── schema.sql                    # Database table creation script
  └── README.md                     # This file

api/
  ├── track.php                     # Endpoint for logging page views
  └── get-active-users.php          # API for real-time active users

includes/
  ├── analytics-tracker.js          # Frontend tracking script
  └── analytics-functions.php       # PHP helper functions for queries
```

### 4. Tracking Script

The tracking script (`analytics-tracker.js`) has been added to all public pages:
- index.php
- about.php
- contact.php
- menu.php
- gallery.php
- order-online.php
- career.php

### 5. Dashboard Access

Access the analytics dashboard at:
```
/admin/site-traffic.php
```

## Features

### Real-Time Tracking
- Page views are tracked automatically on every page load
- Non-blocking asynchronous tracking (doesn't slow down page loads)
- Bot detection and filtering
- Privacy-respecting (no cookies for tracking, uses session IDs)

### Data Collected
- Page URL and title
- Referrer (traffic source)
- User agent (for device/browser detection)
- IP address (for country geolocation)
- Device type (desktop, mobile, tablet)
- Browser and OS information
- Session tracking

### Dashboard Sections
1. **Visitors Over Time** - Line chart showing visitors and page views over time
2. **Traffic Sources** - Doughnut chart showing referrer breakdown
3. **Most Visited Pages** - List of top pages by visits
4. **Top Countries** - Geographic distribution of visitors
5. **Device Types** - Pie chart showing desktop/mobile/tablet breakdown
6. **Browser Usage** - Table showing browser statistics
7. **Active Users** - Real-time count of users active in last 5 minutes

## Configuration

### Time Range Filtering
The dashboard supports time range filtering:
- Today
- This Week (default)
- This Month
- This Year

### GeoIP Service
The system uses ip-api.com (free tier) for country detection:
- 45 requests per minute limit
- No API key required
- Automatically handles rate limiting

For production with high traffic, consider:
- Using a paid GeoIP service
- Caching IP-to-country mappings
- Using MaxMind GeoIP2 database

## Performance Optimization

### Database Indexes
The schema includes optimized indexes for:
- Page URL lookups
- Time-based queries
- Country filtering
- Device type filtering
- Browser filtering

### Query Optimization
- Aggregations use indexed columns
- Queries filter out bots automatically
- Time-based queries use date functions efficiently

### Scaling Considerations
For high-traffic sites (1000+ visitors/day):
1. Consider partitioning the `page_views` table by date
2. Implement data archiving (move old data to archive table)
3. Add query result caching (Redis/Memcached)
4. Use read replicas for dashboard queries

## Security

### Input Sanitization
- All user inputs are sanitized and validated
- SQL injection prevention via prepared statements
- URL validation for page URLs
- XSS prevention via proper escaping

### Privacy
- IP addresses are stored but can be anonymized
- No personal data collection
- Session IDs are randomly generated
- Complies with GDPR (consider adding cookie consent)

## Troubleshooting

### No Data Appearing
1. Check database connection in `db_connection.php`
2. Verify `page_views` table exists
3. Check browser console for JavaScript errors
4. Verify `api/track.php` is accessible
5. Check PHP error logs

### GeoIP Not Working
- Free tier has rate limits (45 req/min)
- Check if IP is local/private (won't be geolocated)
- Verify internet connectivity from server
- Consider using alternative GeoIP service

### Performance Issues
- Check database indexes are created
- Monitor slow query log
- Consider adding query caching
- Archive old data regularly

## Maintenance

### Regular Tasks
1. **Weekly**: Review analytics data accuracy
2. **Monthly**: Archive data older than 1 year
3. **Quarterly**: Review and optimize slow queries
4. **As needed**: Update GeoIP service if switching providers

### Data Archiving
To archive old data:
```sql
CREATE TABLE page_views_archive LIKE page_views;
INSERT INTO page_views_archive SELECT * FROM page_views WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);
DELETE FROM page_views WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);
```

## Support

For issues or questions:
1. Check PHP error logs
2. Check MySQL error logs
3. Verify all files are in correct locations
4. Ensure proper file permissions

## License

This analytics system is part of the Joseph's Pot website project.
