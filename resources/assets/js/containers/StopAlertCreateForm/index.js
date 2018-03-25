import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { withStyles } from 'material-ui/styles';
import { InputAdornment } from 'material-ui/Input';
import {Paper, TextField, Button} from "material-ui";
import { createStopAlert } from "./actions";

const styles = theme => ({
  button: {
    margin: 10,
  },
  buttonGroup: {
    display: 'block',
  },
  form: {
    //
  },
  formLabel: {
    fontSize: 24,
  },
  textField: {
    marginLeft: theme.spacing.unit,
    marginRight: theme.spacing.unit,
    width: 130,
  },
  dateField: {
    marginLeft: theme.spacing.unit,
    marginRight: theme.spacing.unit,
    width: 150,
  },
  root: {
    display: 'flex',
    flexGrow: 1,
  },
});


const Field = (props) => (
  <TextField
    id={props.id}
    label={props.label}
    className={props.className}
    type="text"
    InputLabelProps={{
      shrink: true,
    }}
    margin="normal"
    value={props.value}
    onChange={props.onChange}
    InputProps={props.InputProps}
  />
);

const DateField = (props) => (
  <TextField
    id="purchase-date"
    label="Purchase Date"
    type="date"
    InputLabelProps={{
      shrink: true,
    }}
    className={props.className}
    onChange={props.onChange}
    value={props.value}
  />
);

class StopAlertCreateForm extends React.Component {
  state = {
    symbol: '',
    trail_amount: '',
    initial_price: '',
    purchase_date: '',
  };

  classes = this.props.classes;

  CancelButton = () => (
    <Button variant="raised" className={this.classes.button} onClick={this.props.onRequestClose}>
      Cancel
    </Button>
  );

  SubmitButton = () => (
    <Button variant="raised" className={this.classes.button} onClick={this.handleSubmit}>
      Save
    </Button>
  );

  handleChange = fieldName => event => {
    this.setState({
      [fieldName]: event.target.value,
    });
  };

  handleSubmit = () => {
    this.props.createStopAlert(
      this.state.symbol,
      this.state.trail_amount,
      this.state.initial_price,
      this.state.purchase_date,
      this.props.token
    );
    this.props.onRequestClose();
  };

  render() {
    return (
      <Paper className={this.classes.root}>
        <form className={this.classes.form} noValidate autoComplete="off">
          <Field
            id="symbol"
            label="Symbol"
            onChange={this.handleChange('symbol')}
            className={this.classes.textField}
            value={this.state.symbol}
          />
          <Field
            id="trail-amount"
            label="Trail By..."
            onChange={this.handleChange('trail_amount')}
            className={this.classes.textField} value={this.state.trail_amount}
            InputProps={{
              startAdornment: <InputAdornment position="start">%</InputAdornment>,
            }}
          />
          <Field
            id="initial-price"
            label="Purchase Price"
            onChange={this.handleChange('initial_price')}
            className={this.classes.textField}
            value={this.state.initial_price}
            InputProps={{
              startAdornment: <InputAdornment position="start">$</InputAdornment>,
            }}
          />
          <DateField
            onChange={this.handleChange('purchase_date')}
            className={this.classes.dateField}
            value={this.state.purchase_date}
          />
          <div className={this.classes.buttonGroup}>
            <this.SubmitButton />
            <this.CancelButton />
          </div>
        </form>
      </Paper>
    );
  }
}

StopAlertCreateForm.propTypes = {
  classes: PropTypes.object.isRequired,
  onRequestClose: PropTypes.func,
  token: PropTypes.string,
};

const mapStateToProps = (state) => {
  return {
    token: state.getIn(['auth', 'token']),
  }
};

const mapDispatchToProps = (dispatch) => {
  return {
    createStopAlert: (symbol, trailAmount, initialPrice, purchaseDate, token) => dispatch(createStopAlert(symbol, trailAmount, initialPrice, purchaseDate, token)),
  };
};

export default connect(mapStateToProps, mapDispatchToProps)(withStyles(styles)(StopAlertCreateForm));
