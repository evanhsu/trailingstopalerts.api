import React from 'react';
import PropTypes from 'prop-types';
import ImmutablePropTypes from 'react-immutable-proptypes';
import { connect } from 'react-redux';
import { withStyles } from 'material-ui/styles';
import {Paper, TextField, Button} from "material-ui";
import { updateStopAlert } from "./actions";
import { destroyStopAlert} from "../StopAlertsManager/actions";

const styles = theme => ({
  button: {
    margin: 10,
  },
  deleteButton: {
    margin: 10,
    alignSelf: 'flex-end',
    justifySelf: 'flex-end',
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
    width: 200,
  },
  dateField: {
    marginLeft: theme.spacing.unit,
    marginRight: theme.spacing.unit,
    width: 200,
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
    margin="normal"
    value={props.value}
    onChange={props.onChange}
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

const SubmitButton = (props) => (
  <Button variant="raised" className={props.className} onClick={props.onClick}>
    Save
  </Button>
);

const DeleteButton = (props) => (
  <Button variant="raised" {...props}>
    Delete
  </Button>
);

class StopAlertEditForm extends React.Component {
  state = {
    id: this.props.stopAlert.get('id'),
    trail_amount: this.props.stopAlert.get('trail_amount'),
    initial_price: this.props.stopAlert.get('initial_price'),
    purchase_date: this.props.stopAlert.get('purchase_date'),
  };

  classes = this.props.classes;

  handleChange = fieldName => event => {
    this.setState({
      [fieldName]: event.target.value,
    });
  };

  handleSubmit = () => {
    this.props.updateStopAlert(
      this.state.id,
      this.state.trail_amount,
      this.state.initial_price,
      this.state.purchase_date,
      this.props.token
    );
  };

  handleDeleteStopAlert = (id) => () => {
    this.props.destroyStopAlert(id, this.props.token);
  };

  render() {
    return (
      <Paper className={this.classes.root}>
        <form className={this.classes.form} noValidate autoComplete="off">
          <Field id="trail-amount" label="Trail %" onChange={this.handleChange('trail_amount')}
                 className={this.classes.textField} value={this.state.trail_amount} />
          <Field id="initial-price" label="Purchase Price ($)" onChange={this.handleChange('initial_price')}
                 className={this.classes.textField} value={this.state.initial_price} />
          <DateField onChange={this.handleChange('purchase_date')} className={this.classes.dateField}
                     value={this.state.purchase_date} />
          <SubmitButton onClick={this.handleSubmit} className={this.classes.button} />
          <DeleteButton onClick={this.handleDeleteStopAlert(this.state.id)} className={this.classes.deleteButton} />
        </form>
      </Paper>
    );
  }
}

StopAlertEditForm.propTypes = {
  classes: PropTypes.object.isRequired,
  stopAlert: ImmutablePropTypes.map.isRequired,
  token: PropTypes.string,
};

const mapStateToProps = (state) => {
  return {
    token: state.getIn(['auth', 'token']),
  }
};

const mapDispatchToProps = (dispatch) => {
  return {
    updateStopAlert: (id, trailAmount, initialPrice, purchaseDate, token) => dispatch(updateStopAlert(id, trailAmount, initialPrice, purchaseDate, token)),
    destroyStopAlert: (id, token) => dispatch(destroyStopAlert(id, token)),
  };
};

export default connect(mapStateToProps, mapDispatchToProps)(withStyles(styles)(StopAlertEditForm));
