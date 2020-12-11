ALTER TABLE Orders
	ADD COLUMN PaymentMethod varchar(20) default '';
	ADD COLUMN Address varchar(70) default '';
