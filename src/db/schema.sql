CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
)

CREATE TABLE favorite_stations (
    user_id INT NOT NULL,
    city_id INT NOT NULL,
    station_uid INT NOT NULL,
    note VARCHAR(255),

    PRIMARY KEY (user_id, city_id, station_uid)
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
);