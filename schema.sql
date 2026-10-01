create database movie_review;

use movie_review;

create table user (
	user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'movie_moderator', 'user')NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE genres (
    genre_id INT AUTO_INCREMENT PRIMARY KEY,
    genre_name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE movies (
    movie_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    genre varchar(100),
    year YEAR,
    description TEXT,
    poster VARCHAR(500),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_movies_genre
        FOREIGN KEY (genre_id)
        REFERENCES genres(genre_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,
    movie_id INT NOT NULL,

    rating INT NOT NULL,
    comment TEXT NOT NULL,
    review_date DATETIME DEFAULT CURRENT_TIMESTAMP,

    review_status ENUM(
        'pending',
        'approved',
        'rejected'
    ) NOT NULL DEFAULT 'pending',

    CONSTRAINT chk_review_rating
        CHECK (rating BETWEEN 1 AND 5),

    CONSTRAINT fk_reviews_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_reviews_movie
        FOREIGN KEY (movie_id)
        REFERENCES movies(movie_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE ratings (
    rating_id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,
    movie_id INT NOT NULL,

    rating INT NOT NULL,

    CONSTRAINT chk_rating
        CHECK (rating BETWEEN 1 AND 5),

    CONSTRAINT fk_ratings_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_ratings_movie
        FOREIGN KEY (movie_id)
        REFERENCES movies(movie_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    -- One user can rate one movie only once
    UNIQUE KEY unique_user_movie_rating (user_id, movie_id)
) ENGINE=InnoDB;

CREATE TABLE watchlist (
    watchlist_id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,
    movie_id INT NOT NULL,

    added_date DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_watchlist_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_watchlist_movie
        FOREIGN KEY (movie_id)
        REFERENCES movies(movie_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    -- Prevent the same movie from being added twice
    UNIQUE KEY unique_user_movie_watchlist (user_id, movie_id)
) ENGINE=InnoDB;

INSERT INTO genres (genre_name) VALUES
('Action'),
('Adventure'),
('Animation'),
('Comedy'),
('Crime'),
('Drama'),
('Fantasy'),
('Horror'),
('Romance'),
('Science Fiction'),
('Thriller'),
('Mystery');

INSERT INTO users
(name, email, password, role) VALUES
('System Admin','admin@moviewreview.com','admin123','admin'),
('Movie Moderator','moderator@moviewreview.com','moderator123','movie_moderator'),
('Peter User','peter@example.com','peter123','user'),
('Jane User','jane@example.com','jane123','user'),
('Adam User','adam@example.com','adam123','user'),
('Jess User','jess@example.com','jess123','user');

INSERT INTO movies
(title, genre, year, description, poster) VALUES
('The Dark Knight',Action/Crime,'2008','A masked vigilante fights crime in Gotham City while facing a dangerous criminal mastermind.','dark-knight.jpg'),
('The Lord of the Rings',Fantasy/Adventure,'2001','A young hobbit begins an extraordinary journey to destroy a powerful ring.','lord-of-the-rings.jpg'),
('Toy Story',Family/Comedy,'1995','A group of toys comes to life when humans are not around.','toy-story.jpg'),
('The Hangover',Comedy,'2009','Three friends try to remember what happened during a wild night in Las Vegas.','the-hangover.jpg'),
('The Godfather',Crime,'1972','The story of a powerful crime family and its struggle to maintain control.','the-godfather.jpg'),
('Forrest Gump',Comedy/Romance,'1994','A kind-hearted man experiences several important moments in American history.','forrest-gump.jpg'),
('Harry Potter and the Philosopher''s Stone',Family/Fantasy,'2001','A young boy discovers that he is a wizard and begins his magical education.','harry-potter.jpg'),
('The Conjuring',Horror/Mystery,'2013','Paranormal investigators help a family experiencing terrifying supernatural events.','the-conjuring.jpg'),
('The Notebook',Romantic,'2004','A romantic story about two people whose lives remain connected through the years.','the-notebook.jpg'),
('Inception',Sci-fi/Action,'2010','A skilled thief enters the dreams of others to steal or plant information.','inception.jpg');

INSERT INTO ratings
(user_id, movie_id, rating) VALUES
(3, 1, 5.8),
(4, 2, 4.5),
(6, 3, 4.3),
(5, 10,5.8),
(5, 1, 7.2),
(4, 3, 8.4),
(3, 6, 3.6),
(3, 9, 2.4);

INSERT INTO reviews
(user_id, movie_id, rating, comment, review_status) VALUES
(3, 1, 5.8, 'Amazing movie with great acting and an excellent story.', 'approved'),
(4, 2, 4.5, 'A fantastic adventure movie with beautiful world building.', 'approved'),
(5, 10, 5.8, 'Very interesting story. The dream concept was excellent.', 'approved'),
(4, 3, 8.4, 'A fun and emotional animated movie.', 'approved'),
(3, 6, 3.6, 'A touching movie with a memorable main character.', 'approved'),
(3, 9, 2.4, 'A good romantic movie with an emotional story.', 'pending');

INSERT INTO watchlist
(user_id, movie_id)
VALUES
(4, 4),
(5, 5),
(4, 2),
(3, 2),
(6, 7),
(3, 10);


CREATE INDEX idx_movies_title
ON movies(title);

CREATE INDEX idx_movies_genre
ON movies(genre_id);

CREATE INDEX idx_movies_release_date
ON movies(release_date);

CREATE INDEX idx_reviews_movie
ON reviews(movie_id);

CREATE INDEX idx_reviews_user
ON reviews(user_id);

CREATE INDEX idx_reviews_status
ON reviews(review_status);

CREATE INDEX idx_ratings_movie
ON ratings(movie_id);

CREATE INDEX idx_watchlist_user
ON watchlist(user_id);

SELECT * FROM users;

SELECT * FROM genres;

SELECT * FROM movies;

SELECT * FROM reviews;

SELECT * FROM ratings;

SELECT * FROM watchlist;

SELECT
    m.movie_id,
    m.title,
    g.genre_name,
    m.year,
    m.description,
    m.poster
FROM movies m
JOIN genres g
    ON m.genre_id = g.genre_id
ORDER BY m.title;

SELECT
    m.movie_id,
    m.title,
    ROUND(AVG(r.rating), 1) AS average_rating,
    COUNT(r.rating_id) AS total_ratings
FROM movies m
LEFT JOIN ratings r
    ON m.movie_id = r.movie_id
GROUP BY m.movie_id, m.title
ORDER BY average_rating DESC;

SELECT
    r.review_id,
    m.title,
    u.name AS reviewer,
    r.rating,
    r.comment,
    r.review_date,
    r.review_status
FROM reviews r
JOIN users u
    ON r.user_id = u.user_id
JOIN movies m
    ON r.movie_id = m.movie_id
ORDER BY r.review_date DESC;
